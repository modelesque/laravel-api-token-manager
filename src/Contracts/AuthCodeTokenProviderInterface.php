<?php

namespace Modelesque\ApiTokenManager\Contracts;

use Illuminate\Http\RedirectResponse;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeTokenProvider;

/**
 * @see AuthCodeTokenProvider
 */
interface AuthCodeTokenProviderInterface
{
    /**
     * The key by which session variables will be stored if authorization requires
     * leaving the site.
     *
     * @return string
     */
    public function sessionKey(): string;

    /**
     * Redirect the initial request to the API's authorization/login page where you must
     * grant permission for this client to make requests on your behalf.
     *
     * @return RedirectResponse
     * @throws InvalidConfigException
     */
    public function authorize(): RedirectResponse;

    /**
     * The method that runs inside a controller action corresponding to the PKCE auth
     * code flow's `redirect_uri`. When the request is redirected back to that URI after
     * authorization, this method will handle posting for a new token.
     *
     * @return RedirectResponse
     * @throws InvalidConfigException
     */
    public function handlePostAuthorization(): RedirectResponse;
}