<?php

declare(strict_types=1);

namespace app\controller\index;

use app\service\ProxyConfigService;
use nova\framework\http\Response;

class Api extends BaseController
{
    private ?ProxyConfigService $proxyService = null;

    public function proxyList(): Response
    {
        $result = $this->service()->list();

        return Response::asJson([
            'code' => 200,
            'msg' => 'ok',
            'data' => $result['data'],
            'count' => $result['count'],
        ]);
    }

    public function plugins(): Response
    {
        return Response::asJson([
            'code' => 200,
            'msg' => 'ok',
            'data' => $this->service()->pluginOptions(),
        ]);
    }

    public function save(): Response
    {
        $payload = $this->request->post();
        if (!is_array($payload)) {
            $payload = [];
        }

        [$ok, $msg] = $this->service()->save($payload);
        return $this->json($ok ? 200 : 422, $msg);
    }

    public function remove(): Response
    {
        $domain = (string)$this->request->post('domain', '');
        [$ok, $msg] = $this->service()->remove($domain);
        return $this->json($ok ? 200 : 422, $msg);
    }

    private function service(): ProxyConfigService
    {
        if (!$this->proxyService instanceof ProxyConfigService) {
            $this->proxyService = new ProxyConfigService();
        }

        return $this->proxyService;
    }

    private function json(int $code, string $msg, array $extra = []): Response
    {
        return Response::asJson(array_merge([
            'code' => $code,
            'msg' => $msg,
        ], $extra));
    }
}