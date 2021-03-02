<?php

namespace WebUI\Layout;

use App\Component\ComponentInterface;
use App\Component\Menu\MenuManager;
use App\Traits\CreatableTrait;
use session;

class Header implements ComponentInterface
{
    use CreatableTrait;

    /**
     * @var MenuManager
     */
    private $menuManager;

    public function __construct()
    {
        $this->menuManager = new MenuManager();
    }

    public function setMenuManager($menuManager)
    {
        $this->menuManager = $menuManager;

        return $this;
    }

    public function render()
    {
        return <<<HTML
<header class="container-fluid p-0">
    <div class="container-fluid px-0 pt-3 pb-3" style="background-color: #377cea">
        <div class="container px-0">
            <div class="position-relative" style="display: grid; grid-auto-flow: column; grid-template-columns: 21.5rem 1px auto">
                <div class="d-flex flex-row align-items-center">
                    <img alt="..." src="/static/images/common/header/header_1.svg" style="height: 1.75rem" />
                </div>
                <div class="d-flex flex-row align-items-center bg-white"></div>
                <div class="d-flex flex-row align-items-center justify-content-end">
                    <img alt="..." src="/static/images/common/header/header_2.svg" style="height: 2.5rem" />
                    <img alt="..." src="/static/images/common/header/header_3.svg" style="height: 2.5rem" />
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-light px-0">
        <div class="container px-0">
            <div class="menu menu-primary text-uppercase d-flex flex-row justify-content-between">
                {$this->menuManager->get('primary')->render()}
            </div>
        </div>
    </div>
    <div class="container-fluid bg-secondary px-0">
        <div class="container px-0">
            <div class="menu menu-secondary d-flex flex-row justify-content-between">
                {$this->menuManager->get('secondary')->render()}
                <ul class="nav nav-pills">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                            <i class="fas fa-ellipsis-h"></i> 
                            <i class="glyphicon glyphicon-menu-hamburger"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end text-center">
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Вихід</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
HTML;
    }

    private function getMenu($type = null)
    {

        ob_start();
        include __DIR__.'/'.(null !== $type ? 'submenu.php' : 'menu.php');

        return ob_get_clean();
    }
}

return static function () {
    return Header::create()
        ->setMenuManager(
            MenuManager::create()
                ->set(
                    'primary',
                    [
                        [
                            'href' => '/',
                            'icon' => '<i class="fas fa-house-user"></i>',
                        ],
                        [
                            'href' => '/people',
                            'text' => 'Команда',
                        ],
                        [
                            'href' => '/ppo',
                            'text' => 'Організації',
                        ],
                        [
                            'href' => '/blogs/programs',
                            'text' => 'Успішна Україна',
                        ],
                        [
                            'href' => '/blogs',
                            'text' => 'Публікації',
                        ],
                        [
                            'href' => '/library',
                            'text' => 'Бібліотека',
                        ],
                    ]
                )
                ->set(
                    'secondary',
                    [
                        [
                            'href' => sprintf('/profile/desktop?id=%s', session::get_user_id()),
                            'text' => 'Мой кабинет',
                            'icon' => '<i class="fas fa-chalkboard"></i>',
                        ],
                        [
                            'href' => '/admin/rating',
                            'text' => 'Рейтинги',
                            'icon' => '<i class="fas fa-star-half-alt"></i>',
                        ],
                        [
                            'href' => '/results',
                            'text' => 'Результаты',
                            'icon' => '<i class="fas fa-poll"></i>',
                        ],
                        [
                            'href' => '/groups',
                            'text' => 'Сообщества',
                            'icon' => '<i class="fas fa-users"></i>',
                        ],
                        [
                            'href' => '/polls',
                            'text' => 'Опросы',
                            'icon' => '<i class="fas fa-spell-check"></i>',
                        ],
                        [
                            'href' => '/search',
                            'text' => 'Поиск людей',
                            'icon' => '<i class="fas fa-search"></i>',
                        ],
                    ]
                )
        )
        ->render();
};

