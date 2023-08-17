<?php

namespace app\home\controller;

use app\common\controller\Home;

class Business extends Home
{

    /** 用户模型 */
    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->BusinessModel = model('business.Business');
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
}
