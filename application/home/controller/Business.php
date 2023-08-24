<?php

namespace app\home\controller;

use app\common\controller\Home;
use app\common\library\Email;

use think\Db;

class Business extends Home
{

    /** 用户模型 */
    protected $BusinessModel = null;

    // 课程订单模型
    protected $OrderModel = null;

    // 消费记录模型
    protected $RecordModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->BusinessModel = model('business.Business');
        $this->OrderModel = model('subject.Order');
        $this->RecordModel = model('business.Record');
    }

    public function index()
    {
        return $this->fetch();
    }
    /**
     * 修改资料
     */
    public function profile()
    {
        if ($this->request->isPost()) {
            // 接收表单里的所有参数
            $params = $this->request->param();

            // 封装更新数据
            $data = [
                // 获取登录用户的id作为更新条件
                'id' => $this->LoginBusiness['id'],
                'nickname' => $params['nickname'],
                'email' => $params['email'],
                'gender' => $params['gender']
            ];

            // 如果邮箱更改的话，那么需要重新认证
            if ($params['email'] != $this->LoginBusiness['email']) {
                $data['auth'] = 0;
            }

            // 如果更新密码
            if (!empty($params['password'])) {

                // 验证密码
                if (password_verify($params['password'] . $this->LoginBusiness['salt'], $this->LoginBusiness['password'])) {
                    $this->error('新密码不能与原密码一致');
                }

                // 生成密码盐和哈希密码
                $salt = build_ranstr();
                $hashedPassword = password_hash($params['password'] . $salt, PASSWORD_DEFAULT);

                // 把处理好的密码追加更新数据
                $data['salt'] = $salt;
                $data['password'] = $hashedPassword;
            }

            // 地区
            if (!empty($params['code'])) {

                $path = model('Region')->where(['code' => $params['code']])->value('parentpath');
                if (empty($path)) {
                    $this->error('所选地区不存在');
                }

                $pathArr = explode(',', $path);

                $data['province'] = $pathArr[0] ?? null;
                $data['city'] = $pathArr[1] ?? null;
                $data['district'] = $pathArr[2] ?? null;
            }

            // 头像上传
            if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
                $res = build_upload('avatar');

                if ($res['code'] === 0) {
                    $this->error($res['msg']);
                }

                $data['avatar'] = $res['data'];
            }

            // 更新数据表
            $result = $this->BusinessModel->validate('common/business/Business.profile')->isUpdate(true)->save($data);

            if ($result === false) {
                if (isset($data['avatar']) && $_FILES['avatar']['size']) {
                    @is_file(ltrim($data['avatar'], '/')) && @unlink($data['avatar'], '/');
                }

                $this->error($this->BusinessModel->getError());
            } else {
                if (isset($data['avatar']) && $_FILES['avatar']['size']) {
                    @is_file(ltrim($this->LoginBusiness['avatar'], '/')) && @unlink($this->LoginBusiness['avatar'], '/');
                }

                $this->success('更新成功', url('/home/business/index'));
            }
        }

        return $this->fetch();
    }

    /**
     * 邮箱
     */
    public function email()
    {
        if ($this->request->isPost()) {
            $code = $this->request->param('code', '', 'trim');
            $email = $this->LoginBusiness['email'] ?? '';

            if (empty($code) || empty($email)) {
                $this->error('验证码或邮箱地址不能为空');
            }

            $where = [
                'event' => 'email',
                'code' => $code,
                'email' => $email,
                'times' => 0
            ];

            $Ems = model('Ems');


            // 开启事务
            $this->BusinessModel->startTrans();
            $Ems->startTrans();

            try {
                $ems = $Ems->where($where)->find();

                if (!$ems) {
                    throw new \Exception('验证码错误');
                }

                $BusinessData = [
                    'id' => $this->LoginBusiness['id'],
                    'auth' => 1
                ];

                $BusinessStatus = $this->BusinessModel->isUpdate(true)->save($BusinessData);

                if ($BusinessStatus === false) {
                    throw new \Exception($this->BusinessModel->getError());
                }

                $EmsStatus = $Ems->destroy($ems['id']);

                if ($EmsStatus === false) {
                    throw new \Exception($Ems->getError());
                }

                $this->BusinessModel->commit();
                $Ems->commit();
            } catch (\Exception $e) {
                $this->BusinessModel->rollback();
                $Ems->rollback();
                $this->error($e->getMessage());
            }

            $this->success('认证成功', url('/home/business/index'));
        }

        return $this->fetch();
    }

    /**
     * 发送邮件
     */
    public function send()
    {
        if ($this->request->isAjax()) {
            $email = $this->LoginBusiness['email'] ?? '';
            if (empty($email)) {
                $this->error('邮箱为空');
            }

            // 实例化邮箱验证码模型
            $Ems = model('Ems');

            // 组装条件
            $where = [
                'event' => 'email',
                'email' => $email,
                'times' => 0
            ];
            // 查询数据表是否有这条记录
            $ems = $Ems->where($where)->find();

            if ($ems) {
                $this->error('已发送验证码,请检查您的邮箱');
            }

            // 实例化邮件类
            $mail = new Email();

            // 开启事务
            $Ems->startTrans();
            // 生成验证码
            $code = build_ranstr(4);

            $data = [
                'event' => 'email',
                'email' => $email,
                'times' => 0,
                'ip' => $this->request->ip(),
                'code' => $code
            ];

            $EmsStatus = $Ems->validate('common/Ems')->save($data);
            if ($EmsStatus === false) {
                $this->error($Ems->getError());
            }

            // 正文内容
            $html = "<div>您的邮箱认证码为：<b>$code</b></div>";

            // 获取发送人的邮箱
            $FromEmail = config('site.mail_from');

            $res = $mail->from($FromEmail, '云平台')->subject('邮箱认证')->message($html)->to($email)->send();

            if ($res === false) {
                // 回滚事务
                $Ems->rollback();
                $this->error($mail->getError());
            } else {
                // 提交事务
                $Ems->commit();
                $this->success('发送成功');
            }
        }
    }

    /**
     * 订单列表
     */
    public function order()
    {
        if ($this->request->isAjax()) {

            $page = $this->request->param('page', 1, 'trim');
            $limit = $this->request->param('limit', 20, 'trim');

            $bid = $this->LoginBusiness['id'];


            $where = [
                'busid' => $this->LoginBusiness['id']
            ];

            $count = $this->OrderModel->where($where)->count();

            if ($count <= 0) {
                $this->error('暂无数据');
            }

            $list = $this->OrderModel
                ->with(['subject'])
                ->where($where)
                ->page($page, $limit)
                ->select();

            // 组装数据
            $data = [
                'count' => $count,
                'list' => $list
            ];

            // 是否有数据
            if ($list) {
                $this->success('返回数据', null, $data);
            } else {
                $this->error('暂无数据');
            }
        }
        return $this->fetch();
    }
    /**
     * 消费记录
     */
    public function record()
    {
        if ($this->request->isAjax()) {
            // 接收参数
            $page = $this->request->param('page', 1, 'trim');
            $limit = $this->request->param('limit', 20, 'trim');

            // 获取数据总条数
            $count = $this->RecordModel->where(['busid' => $this->LoginBusiness['id']])->count();

            // 查询数据
            $list = $this->RecordModel
                ->where(['busid' => $this->LoginBusiness['id']])
                ->page($page, $limit)
                ->select();

            // 组装数据
            $data = [
                'count' => $count,
                'list' => $list
            ];

            // 是否有数据
            if ($list) {
                $this->success('返回数据', null, $data);
            } else {
                $this->error('暂无数据');
            }
        }
        return $this->fetch();
    }
}
