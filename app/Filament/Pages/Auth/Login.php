<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use App\Models\User;

/**
 * @property Form $form
 */
class Login extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::pages.auth.login';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }




        $this->form->fill();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();

        // MODO DESARROLLO: Solo autenticación local (sin LDAP)
        $authenticated = false;
        
        // Intentar con email
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $authenticated = Filament::auth()->attempt(
                ['email' => $data['email'], 'password' => $data['password']], 
                $data['remember'] ?? false
            );
        } else {
            // Si no es email, intentar con username
            $authenticated = Filament::auth()->attempt(
                ['username' => $data['email'], 'password' => $data['password']], 
                $data['remember'] ?? false
            );
        }

        // ALTERNATIVA: Habilitar LDAP en producción
        // Descomenta estas líneas cuando tengas LDAP configurado:
        /*
        if (env('LDAP_ENABLED', false)) {
            $username = strstr($data['email'], '@', true);
            $user = User::where('email', $data['email'])
                ->orWhere('username', $username)
                ->first();

            $ldapAuthenticated = $this->authenticateWithLdap($username, $data['password']);
            
            if (!$ldapAuthenticated && $user) {
                $ldapAuthenticated = $this->authenticateWithLdap($user->username, $data['password']);
            }

            if ($ldapAuthenticated && $user) {
                Filament::auth()->login($user);
                $authenticated = true;
            }
        }
        */

        if (!$authenticated) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();
        if (($user instanceof FilamentUser) && 
            (!$user->canAccessPanel(Filament::getCurrentPanel()))) {
            Filament::auth()->logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    // Método LDAP comentado para desarrollo
    protected function authenticateWithLdap(string $username, string $password): bool
    {
        // DESHABILITADO PARA DESARROLLO LOCAL
        return false;
        
        /*
        try {
            $connection = new Connection([
                'hosts' => [env('LDAP_HOST')],
                'port' => env('LDAP_PORT', 389),
                'base_dn' => env('LDAP_BASE_DN'),
                'username' => env('LDAP_USERNAME'),
                'password' => env('LDAP_PASSWORD'),
            ]);

            Container::addConnection($connection);

            $entry = $connection->query()->findBy('samaccountname', $username);

            if (isset($entry)) {
                if ($connection->auth()->attempt($entry["distinguishedname"][0], $password)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => 'Error conectando al servidor LDAP: ' . $e->getMessage(),
            ]);
        }
        */
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email / Usuario')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label(__('filament-panels::pages/auth/login.form.remember.label'));
    }

    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label(__('filament-panels::pages/auth/login.actions.register.label'))
            ->url(filament()->getRegistrationUrl());
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.heading');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}