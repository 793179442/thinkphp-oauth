<?php
namespace Home\Controller;

use Think\Controller;
use Orno\Http\Request;
use Orno\Http\Response;
use Home\Storage;

class AuthcodeController extends Controller
{

//    路由
    private $__router;

//    服务器
    private $__server;

//    请求
    private $__request;

    function __construct()
    {
        parent::__construct();

//        路由设置
        $this->__request = (new Request())->createFromGlobals();
        $this->__router = new \Orno\Route\RouteCollection();

//        设置路由RESTFUL架构
        $this->__router->setStrategy(\Orno\Route\RouteStrategyInterface::RESTFUL_STRATEGY);

//        设置认证服务器
        $this->__server = new \League\OAuth2\Server\AuthorizationServer();
        $this->__server->setSessionStorage(new Storage\SessionStorage());
        $this->__server->setAccessTokenStorage(new Storage\AccessTokenStorage());
        $this->__server->setRefreshTokenStorage(new Storage\RefreshTokenStorage());
        $this->__server->setClientStorage(new Storage\ClientStorage());
        $this->__server->setScopeStorage(new Storage\ScopeStorage());
        $this->__server->setAuthCodeStorage(new Storage\AuthCodeStorage());

//        授权码
        $authCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant();
        $this->__server->addGrantType($authCodeGrant);

//        刷新token
        $refrehTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant();
        $this->__server->addGrantType($refrehTokenGrant);

//        路由设置
        $this->__request = (new Request())->createFromGlobals();
        $this->__router = new \Orno\Route\RouteCollection();
    }

    private function __getCode()
    {
        $server = $this->__server;
        $router = $this->__router;
        $request = $this->__request;

        $dispatcher = $router->getDispatcher();
        try {

//            如果成功返回
            $response = $dispatcher->dispatch(
                $request->getMethod(),
                $request->getPathInfo()
            );
        } catch (\Orno\Http\Exception $e) {
            // A failed response
            $response = $e->getJsonResponse();
            $response->setContent(json_encode(['status_code' => $e->getStatusCode(), 'message' => $e->getMessage()]));
        } catch (\League\OAuth2\Server\Exception\OAuthException $e) {
            $response = new Response(json_encode([
                'error' => $e->errorType,
                'message' => $e->getMessage(),
            ]), $e->httpStatusCode);

            foreach ($e->getHttpHeaders() as $header) {
                $response->headers($header);
            }
        } catch (\Exception $e) {
            $response = new \Orno\Http\Response();
            $response->setStatusCode(500);
            $response->setContent(json_encode(['status_code' => 500, 'message' => $e->getMessage()]));
        } finally {
            // Return the response
            $response->headers->set('Content-type', 'application/json');
            $response->send();
        }
    }

    /**
     * 发放授权码
     */
    public function authorize()
    {
        $server = $this->__server;
        $router = $this->__router;
        $request = $this->__request;

        $router->get('/home/authcode/authorize', function (Request $request) use ($server) {

            // First ensure the parameters in the query string are correct
//            首先确保查询字符串中的参数是正确的

            try {
                $authParams = $server->getGrantType('authorization_code')->checkAuthorizeParams();
            } catch (\League\OAuth2\Server\Exception $e) {
                return new Response(
                    json_encode([
                        'error' => $e->errorType,
                        'message' => $e->getMessage(),
                    ]),
                    $e->httpStatusCode,
                    $e->getHttpHeaders()
                );
            }

            // Normally at this point you would show the user a sign-in screen and ask them to authorize the requested scopes
//            通常在这一点上你会显示用户在屏幕上的一个标志，并要求他们授权的要求范围

            // ...

            if ('pc' !== I('get.display')) {
                header('Content-type:text/html;charset=utf-8');
                echo '将获取以下权限：';
                echo '
                <form method="get" action="' . $authParams['REQUEST_URI'] . '">
                    <input type="hidden" name="display" value="pc">
                    <input type="hidden" name="response_type" value="' . $authParams['response_type'] . '">
                    <input type="hidden" name="client_id" value="' . I('get.client_id').'">
                    <input type="hidden" name="state" value="' . $authParams['state'] . '">
                    <input type="hidden" name="redirect_uri" value="' . $authParams['redirect_uri'] . '">
                    <input type="checkbox" name="scope" value="basic" checked disabled="disabled">Basic details about your account
                    <input type="checkbox" name="scope" value="email">Your email address
                    <input type="checkbox" name="scope" value="photo">Your photo
                    <input type="submit" value="submit">
                </form>
                ';
                exit;
            } else {
                $server->setDefaultScope(I('get.scope'));
            };

            // ...

            // Create a new authorize request which will respond with a redirect URI that the user will be redirected to
//            创建一个新的授权请求，将响应一个重定向URI的用户将被重定向到

            $redirectUri = $server->getGrantType('authorization_code')->newAuthorizeRequest('user', 1, $authParams);

            $response = new Response($redirectUri, 200, [
                'Location' => $redirectUri
            ]);

            return $response;
        });
        $this->__getCode();
    }

    /**
     * 发送令牌
     */
    public function token()
    {
        $server = $this->__server;
        $router = $this->__router;
        $request = $this->__request;

        $router->post('/home/authcode/token', function (Request $request) use ($server) {

            try {
                $response = $server->issueAccessToken();

                return new Response(json_encode($response), 200);
            } catch (\Exception $e) {
                return new Response(
                    json_encode([
                        'error' => $e->errorType,
                        'message' => $e->getMessage(),
                    ]),
                    $e->httpStatusCode,
                    $e->getHttpHeaders()
                );
            }
        });
        $this->__getCode();
    }
}