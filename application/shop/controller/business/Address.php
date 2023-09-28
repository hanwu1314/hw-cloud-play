<?php

namespace app\shop\controller\business;

use think\Controller;

class Address extends Controller
{
    // 收货地址模型
    protected $AddressModel = null;

    public function __construct()
    {
        parent::__construct();

        $this->AddressModel = model('business.Address');
    }
    
    public function index()
    {
        $busid = $this->request->param('busid','','trim');

        $business = model('business.Business')->find($busid);

        if(!$business)
        {
            $this->error('用户不存在');
        }

        $list = $this->AddressModel->where(['busid' => $busid])->select();

        if($list)
        {
            $this->success('查询收货地址数据成功',null,$list);
        }else{
            $this->error('暂无收货地址');
        }
    }

    public function add()
    {
        $params = $this->request->param();

        $business = model('business.Business')->find($params['busid']);

        if(!$business)
        {
            $this->error('用户不存在');
        }

        // 封装插入数据
        $data = [
            'busid' => $params['busid'],
            'consignee' => $params['consignee'],
            'mobile' => $params['mobile'],
            'address' => $params['address'],
            'status' => $params['status'],
        ];

        if($params['status'] == 1)
        {
            $this->AddressModel->where(['busid' => $params['busid']])->update(['status' => 0]);
        }

        // 通过地区码去获取ID路径
        $path = model('Region')->where(['code' => $params['code']])->value('parentpath');

        // 如果路径为空就提示
        if(empty($path))
        {
            $this->error('所选地区不存在');
        }

        // 转成数组
        $pathArr = explode(',',$path);

        // 赋值
        $data['province'] = $pathArr[0] ?? null;
        $data['city'] = $pathArr[1] ?? null;
        $data['district'] = $pathArr[2] ?? null;

        // 插入数据表
        $result = $this->AddressModel->validate('common/business/Address')->save($data);

        if($result === false)
        {
            $this->error($this->AddressModel->getError());
        }else{
            $this->success('添加成功');
        }
    }

    
}
