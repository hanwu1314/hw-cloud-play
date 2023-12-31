<?php

namespace app\common\model\business;

use think\Env;
use think\Model;

class Business extends Model
{
    /** 指定的数据表 */
    protected $name = 'business';
    /** 开启自动写入时间戳 */
    protected $autoWriteTimestamp = true;
    /** 定义时间戳字段名 */
    protected $createTime = 'createtime';
    /** 更新时间 */
    protected $updateTime = false;
    /** 追加字段 */
    protected $append = [
        'mobile_text',
        'avatar_cdn',
        'region_text'
    ];
    /**
     * 获取手机号将其加密
     */
    public function getMobileTextAttr($value, $data)
    {
        $mobile = $data['mobile'] ?? '';
        if (empty($mobile)) {
            return false;
        }
        return substr_replace($mobile, '****', 3, 4);
    }
    /**
     * 获取头像
     */
    public function getAvatarCdnAttr($value, $data)
    {
        $avatar = $data['avatar'] ?? '';

        if (empty($avatar)) {
            $avatar = 'assets/home/images/avatar.jpg';
        }

        // 获取网站域名
        $cdn = Env::get('site.url',config('site.url'));


        return $cdn . $avatar;
    }

    public function getRegionTextAttr($value,$data)
    {
        $region_text = '';

        $province = model('Region')->where(['code' => $data['province']])->value('name');
        $city = model('Region')->where(['code' => $data['city']])->value('name');
        $district = model('Region')->where(['code' => $data['district']])->value('name');

        if($province)
        {
            $region_text = $province;
        }

        if($city)
        {
            $region_text .= '-' . $city;
        }

        if($district)
        {
            $region_text .=  '-' . $district;
        }

        return $region_text;
    }
}
