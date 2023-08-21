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

    public function __construct()
    {
        parent::__construct();

        $this->SubjectModel = model('subject.Subject');
        $this->ChapterModel = model('subject.Chapter');
        $this->CommentModel = model('subject.Comment');
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
}
