<?php

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Constants\RedisKey;
use App\Constants\StatusCode;
use App\Controller\AbstractController;
use App\Service\Auth\LoginService;
use App\Middleware\RequestMiddleware;
use App\Utils\Redis;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Phper666\JWTAuth\JWT;
use function Sodium\add;


class LoginController extends AbstractController
{

    public function login()
    {
        $address = $this->request->input('address') ?? '';
        $params = [
            'address' => $address,
            'login_sign' => $this->request->input('login_sign') ?? '',
            'chain_id' => $this->request->input('chain_id') ?? '',
        ];
        $rules = [
            'address' => 'required',
            'login_sign' => 'required',
            'chain_id' => 'required',
        ];
        $message = [
            'address.required' => ' address missing',
            'login_sign.required' => ' login_sign missing',
            'chain_id.required' => ' chain_id missing',
        ];
        $this->verifyParams($params, $rules, $message);

        $responseData = LoginService::getInstance()->login($params);

        //处理个人nft数据
        $redis = Redis::getInstance();
        $redis->lPush(RedisKey::FETCH_NFT_BY_ADDRESS, json_encode(['address' => $address]));
        return $this->success($responseData);
    }

    /**
     * 注册操作
     * @RequestMapping(path="register", methods="post")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function register()
    {
        $params = [
            'username' => $this->request->input('username') ?? '',
            'password' => $this->request->input('password') ?? '',
            'password_confirmation' => $this->request->input('password_confirmation') ?? '',
            'desc' => $this->request->input('desc') ?? '',
            'code_key' => $this->request->input('code_key') ?? '',
            'captcha' => $this->request->input('captcha') ?? '',
        ];
        $rules = [
            'username' => 'required|min:4|max:18|unique:users',
            'password' => 'required|min:6|max:18|confirmed:password_confirmation',
            'password_confirmation' => 'required|min:6|max:18',
            'desc' => 'required',
            'code_key' => 'required',
            'captcha' => 'required',
        ];
        $message = [
            'username.required' => ' username 缺失',
            'username.unique' => '该用户名已经存在',
            'username.min' => '[username]最少4位',
            'username.max' => '[username]最多18位',
            'desc.required' => ' desc 缺失',
            'password.required' => ' password 缺失',
            'password_confirmation.required' => ' password 缺失',
            'password.min' => ' password 最少6位数',
            'password_confirmation.min' => ' password 最少6位数',
            'password.max' => ' password 最多18位数',
            'password_confirmation.max' => ' password 最多18位数',
            'code_key.required' => '验证码KEY缺失',
            'captcha.required' => '验证码缺失',
        ];
        $this->verifyParams($params, $rules, $message);

        $result = LoginService::getInstance()->register($params);
        if (!$result) $this->throwExp(StatusCode::ERR_REGISTER_ERROR, '注册失败');

        return $this->successByMessage('注册成功, 跳转登陆中...');
    }


    /**
     * 初始化操作
     * @RequestMapping(path="initialization", methods="get")
     * @Middlewares({
    @Middleware(RequestMiddleware::class)
     *     })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function initialization()
    {
        $list = LoginService::getInstance()->initialization();
        return $this->success($list);
    }

    /**
     * 获取前端路由
     * @RequestMapping(path="routers", methods="get")
     * @Middlewares({
    @Middleware(RequestMiddleware::class)
     * })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getRouters()
    {
        $list = LoginService::getInstance()->getRouters();
        return $this->success($list);
    }

    /**
     * 退出登录操作
     * @RequestMapping(path="logout", methods="post")
     * @Middlewares({
    @Middleware(RequestMiddleware::class)
     *     })
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function logOut()
    {
        $this->jwt->logout();
        return $this->success([], '退出登录成功');
    }
}
