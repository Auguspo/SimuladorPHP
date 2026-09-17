<?php

namespace App\Controllers;

class PageController
{
    private function render(string $viewPath): void
    {
        require_once PROJECT_ROOT . '/src/views/' . $viewPath . '.html';
    }

    public function home(): void
    {
        $this->render('home');
    }

    public function estadisticas(): void
    {
        $this->render('estadisticas');
    }

    public function participantes(): void
    {
        $this->render('participantes');
    }

    public function conductor(): void
    {
        $this->render('conductor');
    }

    public function sesion(): void
    {
        $this->render('sesion');
    }

    public function ranking(): void
    {
        $this->render('ranking');
    }
}
