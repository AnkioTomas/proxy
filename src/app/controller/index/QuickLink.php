<?php

declare(strict_types=1);

namespace app\controller\index;

use app\db\Dao\QuickLinkDao;
use app\db\Model\QuickLinkModel;
use nova\framework\http\Response;

class Quicklink extends BaseController
{
    public function list(): Response
    {
        $rows = QuickLinkDao::getInstance()->listByUser($this->currentUser->id);

        $data = [];
        foreach ($rows as $row) {
            $data[] = $row->toArray();
        }

        return Response::asJson([
            'code' => 200,
            'data' => $data,
        ]);
    }

    public function save(): Response
    {
        $id = (int)$this->request->post('id', 0);
        $url = $this->normalizeUrl((string)$this->request->post('url', ''));
        if ($url === null) {
            return Response::asJson(['code' => 400, 'msg' => '请输入合法的 http(s) URL'], 400);
        }

        $title = trim((string)$this->request->post('title', ''));
        if ($title === '') {
            $title = $this->defaultTitle($url);
        }

        $dao = QuickLinkDao::getInstance();
        $duplicate = $dao->find(null, [
            'user_id' => $this->currentUser->id,
            'url' => $url,
        ]);
        if ($duplicate !== null && ($id <= 0 || (int)$duplicate->id !== $id)) {
            return Response::asJson(['code' => 400, 'msg' => '该网站已存在'], 400);
        }

        if ($id > 0) {
            $model = $dao->findById($id);
            if ($model === null || $model->user_id !== $this->currentUser->id) {
                return Response::asJson(['code' => 404, 'msg' => '记录不存在'], 404);
            }
            $model->url = $url;
            $model->title = $title;
            $dao->updateModel($model);
        } else {
            $model = new QuickLinkModel();
            $model->user_id = $this->currentUser->id;
            $model->url = $url;
            $model->title = $title;
            $model->sort = (int)$this->request->post('sort', 0);
            $model->id = $dao->insertModel($model);
        }

        return Response::asJson([
            'code' => 200,
            'msg' => '保存成功',
            'data' => $model->toArray(),
        ]);
    }

    public function remove(): Response
    {
        $id = (int)$this->request->post('id', 0);
        if ($id <= 0) {
            return Response::asJson(['code' => 400, 'msg' => '参数错误'], 400);
        }

        $dao = QuickLinkDao::getInstance();
        $model = $dao->findById($id);
        if ($model === null || $model->user_id !== $this->currentUser->id) {
            return Response::asJson(['code' => 404, 'msg' => '记录不存在'], 404);
        }

        $dao->delete()->where(['id' => $id, 'user_id' => $this->currentUser->id])->commit();

        return Response::asJson(['code' => 200, 'msg' => '删除成功']);
    }

    private function normalizeUrl(string $raw): ?string
    {
        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        if (!preg_match('/^https?:\/\//i', $value)) {
            $value = 'https://' . $value;
        }

        $parts = parse_url($value);
        if ($parts === false || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if ($scheme !== 'http' && $scheme !== 'https') {
            return null;
        }

        $path = $parts['path'] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        $url = $scheme . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $url .= ':' . $parts['port'];
        }
        $url .= $path;
        if (!empty($parts['query'])) {
            $url .= '?' . $parts['query'];
        }

        return $url;
    }

    private function defaultTitle(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return $url;
        }

        return preg_replace('/^www\./', '', $host) ?: $host;
    }
}
