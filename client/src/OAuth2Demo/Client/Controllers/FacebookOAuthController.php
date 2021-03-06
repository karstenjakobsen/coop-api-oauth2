<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class FacebookOAuthController extends BaseController
{
    public static function addRoutes($routing)
    {
        $routing->get('/facebook/oauth/start', array(new self(), 'redirectToAuthorization'))->bind('facebook_authorize_start');
        $routing->get('/facebook/oauth/handle', array(new self(), 'receiveAuthorizationCode'))->bind('facebook_authorize_redirect');

        $routing->get('/coop/facebook/share', array(new self(), 'shareProgressOnFacebook'))->bind('facebook_share_place');
    }

    /**
     * This page actually redirects to the Facebook authorize page and begins
     * the typical, "auth code" OAuth grant type flow.
     *
     * @return RedirectResponse
     */
    public function redirectToAuthorization()
    {
		$facebook = $this->createFacebook();

		$redirectUrl = $this->generateUrl(
			'facebook_authorize_redirect',
			array(),
			true
		);

		$url = $facebook->getLoginUrl(array(
			'redirect_uri' => $redirectUrl,
			'scope' => array('public_profile', 'email', 'user_friends')
		));

		return $this->redirect($url);
    }

    /**
     * This is the URL that Facebook will redirect back to after the user approves/denies access
     *
     * Here, we will get the authorization code from the request, exchange
     * it for an access token, and maybe do some other setup things.
     *
     * @param  Application             $app
     * @param  Request                 $request
     * @return string|RedirectResponse
     */
    public function receiveAuthorizationCode(Application $app, Request $request)
    {

		$facebook = $this->createFacebook();
		$userId = $facebook->getUser();

		if (!$userId) {
			return $this->render('failed_authorization.twig', array(
				'response' => $request->query->all()
			));
		}

		$user = $this->getLoggedInUser();
		$user->facebookUserId = $userId;
		$this->saveUser($user);

		return $this->redirect($this->generateUrl('home'));
    }

	private function createFacebook()
	{
		$config = array(
			'appId' => '1831521120452454',
			'secret' => '1ae8caed6ed9a7471262c67c62e4ca74',
			'allowSignedRequest' => false
		);

		return new \Facebook($config);
	}

    /**
     * Posts your current status to your Facebook wall then redirects to
     * the homepage.
     *
     * @return RedirectResponse
     */
    public function shareProgressOnFacebook()
    {
        die('Todo: Use Facebook\'s API to post to someone\'s feed');

        return $this->redirect($this->generateUrl('home'));
    }
}
