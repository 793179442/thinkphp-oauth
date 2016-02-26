<?php
namespace Home\Controller;
use Think\Controller;
use Orno\Http\Request;
use Orno\Http\Response;
use Home\Storage;

class AuthcodeController extends Controller {

    //    ·��
    private $__router;

//    ������
    private $__server;

//    ����
    private $__request;

    function __construct()
    {
        parent::__construct();

//        ·������
        $this->__request = (new Request())->createFromGlobals();
        $this->__router = new \Orno\Route\RouteCollection();
        $this->__router->setStrategy(\Orno\Route\RouteStrategyInterface::RESTFUL_STRATEGY);

//        ������֤������
        $this->__server = new \League\OAuth2\Server\AuthorizationServer();
        $this->__server->setSessionStorage(new Storage\SessionStorage());
        $this->__server->setAccessTokenStorage(new Storage\AccessTokenStorage());
        $this->__server->setRefreshTokenStorage(new Storage\RefreshTokenStorage());
        $this->__server->setClientStorage(new Storage\ClientStorage());
        $this->__server->setScopeStorage(new Storage\ScopeStorage());
        $this->__server->setAuthCodeStorage(new Storage\AuthCodeStorage());

        $authCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant();
        $this->__server->addGrantType($authCodeGrant);

        $refrehTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant();
        $this->__server->addGrantType($refrehTokenGrant);

// Routing setup
        $this->__request = (new Request())->createFromGlobals();
        $this->__router = new \Orno\Route\RouteCollection();
    }

    /**
     * Ĭ����ҳ
     */
    public function index()
    {

    }
    private function __getCode()
    {
        $server = $this->__server;
        $router = $this->__router;
        $request = $this->__request;

        $dispatcher = $router->getDispatcher();

        try {
            // A successful response
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
                'error'     =>  $e->errorType,
                'message'   =>  $e->getMessage(),
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
     * ������Ȩ��
     */
    public function authorize()
    {
        $server = $this->__server;
        $router = $this->__router;
        $request = $this->__request;

        $router->get('/home/authcode/authorize', function (Request $request) use ($server) {

            // First ensure the parameters in the query string are correct
//            ����ȷ����ѯ�ַ����еĲ�������ȷ��

            try {
                $authParams = $server->getGrantType('authorization_code')->checkAuthorizeParams();
            } catch (\League\OAuth2\Server\Exception $e) {
                return new Response(
                    json_encode([
                        'error'     =>  $e->errorType,
                        'message'   =>  $e->getMessage(),
                    ]),
                    $e->httpStatusCode,
                    $e->getHttpHeaders()
                );
            }

//            $server->$this->setDefaultScope('1 2');
            // Normally at this point you would show the user a sign-in screen and ask them to authorize the requested scopes
//            ͨ������һ���������ʾ�û�����Ļ�ϵ�һ����־����Ҫ��������Ȩ��Ҫ��Χ

            // ...

            // ...

            // ...

            // Create a new authorize request which will respond with a redirect URI that the user will be redirected to
//            ����һ���µ���Ȩ���󣬽���Ӧһ���ض���URI���û������ض���

            $redirectUri = $server->getGrantType('authorization_code')->newAuthorizeRequest('user', 1, $authParams);

            $response = new Response($redirectUri, 200, [
                'Location'  =>  $redirectUri
            ]);
            return $response;
        });
        $this->__getCode();
    }

    /**
     * ��������
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
                        'error'     =>  $e->errorType,
                        'message'   =>  $e->getMessage(),
                    ]),
                    $e->httpStatusCode,
                    $e->getHttpHeaders()
                );
            }
        });
        $this->__getCode();
    }
}