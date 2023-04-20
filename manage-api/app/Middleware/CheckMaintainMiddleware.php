<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Constants\StatusCode;
use App\Exception\Handler\BusinessException;
use App\Model\System\GlobalConfig;
use App\Service\Auth\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Constants\WhiteRoute;

/**
 * 监测系统是否在维护中
 * Class CheckMaintainMiddleware
 * @package App\Middleware
 * @Author YiYuan-Lin
 * @Date: 2021/06/17
 */
class CheckMaintainMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            // Headers 可以根据实际情况进行改写。
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

        $writeRoute = WhiteRoute::ROUTE_LIST;
        if (in_array($request->getUri()->getPath(), $writeRoute)) return $handler->handle($request);

        //获取当前用户
        $user = UserService::getInstance()->getUserInfoByToken();
        //判断是否是超级管理员
        if ($user->hasRole('super_admin')) return $handler->handle($request);

        // 获取后台维护状态
        $maintain = GlobalConfig::getOneByKeyName('maintain_switch');
        $isMaintain = (bool)$maintain['data'];
        // 判断后台是否处于维护状态
        if ($isMaintain) throw new BusinessException(StatusCode::ERR_MAINTAIN, '系统维护中，如有需要请联系管理员');

        // 获取后台简易维护状态
        $simpleMaintain = GlobalConfig::getOneByKeyName('simple_maintain_switch');
        $isSimpleMaintain = (bool)$simpleMaintain['data'];
        $httpMethod = $request->getMethod();

        // 判断后台是否处于简易维护状态
        if ($isSimpleMaintain && $httpMethod != 'GET') {
            throw new BusinessException(StatusCode::ERR_MAINTAIN, '系统维护中，只能执行查询操作, 如有需要请联系管理员');
        }
        return $handler->handle($request);
    }
}
