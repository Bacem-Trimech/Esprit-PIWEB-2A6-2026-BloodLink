<?php
/** Shared helpers for all controllers: render a view in a layout, redirect, flash messages, access checks. */
abstract class BaseController
{
    public function __construct(protected AuthService $auth, protected UserService $users) {}

    protected function render(string $view, array $data = [], string $layout = 'front'): void
    {
        $user = $this->auth->check();                 // User or null
        $this->auth->csrfToken();                     // makes sure csrf_field() has a token
        $can = fn(string $permission): bool => $this->auth->can($permission);
        extract($data);
        ob_start();
        require VIEWS . '/' . $view . '.php';
        $content = ob_get_clean();
        $title = $title ?? APP_NAME;
        require VIEWS . '/layout_' . $layout . '.php';
    }

    protected function redirect(string $action, array $q = []): void
    {
        header('Location: ' . url($action, $q));
        exit;
    }

    protected function flash(string $type, string $message): void { $_SESSION['flash'] = [$type, $message]; }

    protected function spaceOf(User $u): string { return $u->getRoleSide() === 'back' ? 'admin' : 'dashboard'; }

    /** Login required + permission required, otherwise redirect (not logged in) or 403. Returns the user. */
    protected function need(string $permission): User
    {
        $u = $this->auth->check();
        if (!$u) $this->redirect('login');
        if (!$this->auth->can($permission)) $this->forbid();
        return $u;
    }

    protected function forbid(): void
    {
        http_response_code(403);
        $this->render('front/403', ['title' => 'Access denied']);
        exit;
    }

    protected function missing(string $layout = 'front'): void
    {
        http_response_code(404);
        $this->render($layout === 'back' ? 'back/404' : 'front/404', ['title' => 'Not found'], $layout);
        exit;
    }
}
