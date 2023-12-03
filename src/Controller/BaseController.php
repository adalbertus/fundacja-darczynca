<?php

namespace App\Controller;

use App\Constants\SessionKeys;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;



class BaseController extends AbstractController
{
    protected function addFlashInfo(string $message): void
    {
        $this->addFlash('info', $message);
    }

    protected function addFlashSuccess(string $message): void
    {
        $this->addFlash('success', $message);
    }
    protected function addFlashWarning(string $message): void
    {
        $this->addFlash('warning', $message);
    }
    protected function addFlashError(string $message): void
    {
        $this->addFlash('danger', $message);
    }

    protected function saveToSession(Request $request, $key, $value)
    {
        if (!is_null($value)) {
            $session = $request->getSession();
            $session->set($key, $value);
        }
    }

    protected function getFromSession(Request $request, $key, $default = '')
    {
        $session = $request->getSession();
        $value = $session->get($key, $default);
        return $value;
    }

    protected function saveRefererUrl(Request $request)
    {
        $this->saveToSession($request, SessionKeys::REFERER_URL, $request->headers->get('referer'));
    }

    protected function getRefererUrl(Request $request): mixed
    {
        $url = '';
        $session = $request->getSession();
        if ($session->has(SessionKeys::REFERER_URL)) {
            $url = $session->get(SessionKeys::REFERER_URL, '');
            $session->remove(SessionKeys::REFERER_URL);
        }
        return $url;
    }

    protected function prepareCriteria(InputBag $query): array
    {
        $criteria = [];
        foreach ($query as $key => $value) {
            $criteria[$key] = $value;
        }
        return $criteria;
    }
}