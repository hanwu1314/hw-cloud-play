<?php

namespace app\home\controller\subject;

use app\common\controller\Home;

class Subject extends Home
{
    protected $noNeedLogin = ['info'];

    // 课程模型
    protected $SubjectModel = null;

    // 课程章节模型
    protected $ChapterModel = null;

    // 课程评论模型
    protected $CommentModel = null;

    // 课程订单模型
    protected $OrderModel = null;

    // 用户模型
    protected $BusinessModel = null;

    // 消费记录模型
    protected $RecordModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->SubjectModel = model('subject.Subject');
        $this->ChapterModel = model('subject.Chapter');
        $this->CommentModel = model('subject.Comment');
        $this->OrderModel = model('subject.Order');
        $this->BusinessModel = model('business.Business');
        $this->RecordModel = model('business.Record');
    }

    public function search()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->param('page', 1, 'trim');
            $limit = $this->request->param('limit', 10, 'trim');
            $search = $this->request->param('search', '', 'trim');

            // 条件数组
            $where = [];

            if ($search) {
                $where['title'] = ['LIKE', "%$search%"];
            }

            $count = $this->SubjectModel->where($where)->count();

            $list = $this->SubjectModel->with(['category'])->where($where)->order('createtime DESC')->page($page, $limit)->select();

            if ($list) {
                $this->success('查询成功', null, ['count' => $count, 'list' => $list]);
            } else {
                $this->error('暂无数据', null, ['count' => 0, 'list' => []]);
            }
        }

        return $this->fetch();
    }

    public function info()
    {
        $id = $this->request->param('id', '', 'trim');

        $subject = $this->SubjectModel->find($id);

        if (!$subject) {
            $this->error('课程不存在');
        }

        // 获取cookie里面的信息
        $business = cookie('LoginBusiness');

        $subject['like_status'] = false;

        if ($business) {
            $likeArr = explode(',', $subject['likes']);

            // 过滤空元素
            $likeArr = array_filter($likeArr);

            if (in_array($business['id'], $likeArr)) {
                $subject['like_status'] = true;
            }
        }

        // 获取该课程的章节的总数
        $count = $this->ChapterModel->where(['subid' => $id])->count();

        // 获取该课程的所有章节的数据
        $ChapterList = $this->ChapterModel->where(['subid' => $id])->order('id ASC')->select();

        // 获取对该课程的评价
        $CommentList = $this->CommentModel->with(['business'])->where(['subid' => $id])->limit(5)->order('createtime DESC')->select();

        $this->assign([
            'subject' => $subject,
            'count' => $count,
            'ChapterList' => $ChapterList,
            'CommentList' => $CommentList
        ]);

        return $this->fetch();
    }

    /**
     * 点赞
     */
    public function like()
    {
        $subid = $this->request->param('subid', 0, 'trim');
        // 查询课程是否存在
        $subject = $this->SubjectModel->find($subid);

        if (!$subject) {
            $this->error('课程不存在');
        }

        // 分割数组
        $likeArr = explode(',', $subject['likes']);
        // 过滤空元素
        $likeArr = array_filter($likeArr);

        $msg = '';

        if (in_array($this->LoginBusiness['id'], $likeArr)) {
            $index = array_search($this->LoginBusiness['id'], $likeArr);

            if ($index !== false) {
                unset($likeArr[$index]);
                $msg = '取消点赞';
            } else {
                $this->error('无法取消点赞');
            }
        } else {
            $likeArr[] = $this->LoginBusiness['id'];

            $msg = '点赞';
        }

        // 封装更新数据
        $data = [
            'id' => $subid,
            'likes' => implode(',', $likeArr)
        ];

        $result = $this->SubjectModel->isUpdate(true)->save($data);

        if ($result === false) {
            $this->error($msg . '失败');
        } else {
            $this->success($msg . '成功');
        }
    }
    /**
     * 播放
     */
    public function play()
    {

        $subid = $this->request->param('subid', 0, 'trim');
        $cid = $this->request->param('cid', 0, 'trim');
        // 判断课程是否存在
        $subject = $this->SubjectModel->find($subid);

        if (empty($subject)) {
            $this->error('课程不存在');
        }
        // 组装订单查询条件
        $OrderWhere = [
            'busid' => $this->LoginBusiness['id'],
            'subid' => $subid
        ];
        // 查询该用户是否购买了该课程
        $order = $this->OrderModel->where($OrderWhere)->find();

        if (empty($order)) {
            $this->error('请先购买课程', null, ['buy' => true]);
        }
        // 查询用户要播放的课程章节
        $where = [
            'subid' => $subid
        ];
        if ($cid) {
            $where['id'] = $cid;
        }
        $chapter = $this->ChapterModel->where($where)->order("id ASC")->find();
        if ($chapter) {
            $this->success('查询成功', null, $chapter);
        } else {
            $this->error('暂无章节播放地址');
        }
    }
    /**
     * 购买课程
     */
    public function buy()
    {
        // 获取课程id
        $subId = $this->request->param('subid', 0, 'trim');
        // 判断课程是否存在
        $subject = $this->SubjectModel->find($subId);
        // 获取用户id
        $busId = $this->LoginBusiness['id'];

        if (!$subject) {
            $this->error('课程不存在');
        }

        // 判断当前登录用户是否购买过
        $where = [
            'subid' => $subId,
            'busid' => $busId
        ];

        $order = $this->OrderModel->where($where)->find();

        if ($order) {
            $this->error('您已经购买过了该课程，无须重复购买');
        }

        // 课程价格
        $price = $subject['price'];
        // 个人余额
        $money = $this->LoginBusiness['money'];
        // 判断余额是否能够购买 余额-价格
        $updateMoney = bcsub($money, $price, 2);
        if ($updateMoney < 0) {
            $this->error('余额不足，请先充值');
        }

        // 开启事务操作
        $this->BusinessModel->startTrans();
        $this->OrderModel->startTrans();
        $this->RecordModel->startTrans();

        // 插入订单表
        // 封装订单数据
        $OrderData = [
            'subid' => $subId,
            'busid' => $busId,
            'total' => $price,
            'code' => generateSnowflakeId(1),
        ];

        // 把数据插入数据表
        $OrderStatus = $this->OrderModel->validate('common/subject/Order')->save($OrderData);

        if ($OrderStatus === false) {
            $this->error($this->OrderModel->getError());
        }

        // 更新用户数据
        $BusinessData = [
            'id' => $this->LoginBusiness['id'],
            'money' => $updateMoney
        ];

        // 自定义一个验证器
        $validate = [
            // 规则
            [
                'money' => ['number', '>=:0'],
            ],
            // 错误信息
            [
                'money.number' => '余额必须是数字类型',
                'money.>=' => '余额必须大于等于0元',
            ]
        ];

        $BusinessStatus = $this->BusinessModel->validate(...$validate)->isUpdate(true)->save($BusinessData);

        if ($BusinessStatus === false) {
            $this->OrderModel->rollback();
            $this->error($this->BusinessModel->getError());
        }

        // 封装用户消费记录
        $RecordData = [
            'total' => $subject['price'],
            'busid' => $this->LoginBusiness['id'],
            'content' => "购买课程：【{$subject['title']}】花费了 ￥{$subject['price']} 元"
        ];

        $RecordStatus = $this->RecordModel->validate('common/business/Record')->save($RecordData);

        try {
            if ($RecordStatus === false) {
                throw new \Exception($this->RecordModel->getError());
            }
            if ($OrderStatus === false || $BusinessStatus === false || $RecordStatus === false) {
                throw new \Exception('购买课程失败');
            }
            $this->OrderModel->commit();
            $this->BusinessModel->commit();
            $this->RecordModel->commit();
        } catch (\Exception $e) {
            $this->OrderModel->rollback();
            $this->BusinessModel->rollback();
            $this->RecordModel->rollback();
            $this->error($e->getMessage());
        }
        $this->success('购买课程成功');
    }
}
