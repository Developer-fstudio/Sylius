<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\Client\ApiClientInterface;
use Sylius\Behat\Client\ApiSecurityClientInterface;
use Sylius\Behat\Client\RequestFactoryInterface;
use Sylius\Behat\Client\RequestInterface;
use Sylius\Behat\Client\ResponseCheckerInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Component\HttpFoundation\Request as HTTPRequest;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class LoginContext implements Context
{
    private ?RequestInterface $request = null;

    public function __construct(
        private ApiSecurityClientInterface $apiSecurityClient,
        private ApiClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private ResponseCheckerInterface $responseChecker,
        private string $apiUrlPrefix
    ) {
    }

    /**
     * @When I want to log in
     */
    public function iWantToLogIn(): void
    {
        $this->apiSecurityClient->prepareLoginRequest();
    }

    /**
     * @When I specify the username as :username
     */
    public function iSpecifyTheUsername(string $username): void
    {
        $this->apiSecurityClient->setEmail($username);
    }

    /**
     * @When I specify the password as :password
     */
    public function iSpecifyThePasswordAs(string $password): void
    {
        $this->apiSecurityClient->setPassword($password);
    }

    /**
     * @When I log in
     */
    public function iLogIn(): void
    {
        $this->apiSecurityClient->call();
    }

    /**
     * @When I want to reset password
     */
    public function iWantToResetPassword(): void
    {
        $this->request = $this->requestFactory->create('admin', 'reset-password-requests', 'Bearer');
    }

    /**
     * @When I specify email as :email
     */
    public function iSpecifyEmailAs(string $email): void
    {
        $this->request->updateContent(['email' => $email]);
    }

    /**
     * @When I reset it
     * @When I try to reset it
     */
    public function iResetIt(): void
    {
        $this->client->executeCustomRequest($this->request);
    }

    /**
     * @When /^(I) follow the instructions to reset my password$/
     */
    public function iFollowTheInstructionsToResetMyPassword(AdminUserInterface $admin): void
    {
        $this->request = $this->requestFactory->custom(
            sprintf('%s/admin/reset-password-requests/%s', $this->apiUrlPrefix, $admin->getPasswordResetToken()),
            HttpRequest::METHOD_PATCH,
        );
    }

    /**
     * @When I specify my new password as :password
     */
    public function iSpecifyMyNewPassword(string $password): void
    {
        $this->request->updateContent(['newPassword' => $password]);
    }

    /**
     * @When I confirm my new password as :password
     */
    public function iConfirmMyNewPassword(string $password): void
    {
        $this->request->updateContent(['confirmNewPassword' => $password]);
    }

    /**
     * @Then I should be logged in
     */
    public function iShouldBeLoggedIn(): void
    {
        Assert::true($this->apiSecurityClient->isLoggedIn(), 'Admin should be logged in, but they are not.');
    }

    /**
     * @Then I should not be logged in
     */
    public function iShouldNotBeLoggedIn(): void
    {
        Assert::false($this->apiSecurityClient->isLoggedIn(), 'Admin should not be logged in, but they are.');
    }

    /**
     * @Then I should be notified about bad credentials
     */
    public function iShouldBeNotifiedAboutBadCredentials(): void
    {
        Assert::same($this->apiSecurityClient->getErrorMessage(), 'Invalid credentials.');
    }

    /**
     * @Then I should be able to log in as :username authenticated by :password password
     */
    public function iShouldBeAbleToLogInAsAuthenticatedByPassword(string $username, string $password): void
    {
        $this->logIn($username, $password);
        $this->iShouldBeLoggedIn();
    }

    /**
     * @Then I should not be able to log in as :username authenticated by :password password
     */
    public function iShouldNotBeAbleToLogInAsAuthenticatedByPassword(string $username, string $password): void
    {
        $this->logIn($username, $password);
        $this->iShouldNotBeLoggedIn();
    }

    /**
     * @Then I should be notified that email with reset instruction has been sent
     */
    public function iShouldBeNotifiedThatEmailResetInstructionHasBeenSent(): void
    {
        Assert::same($this->client->getLastResponse()->getStatusCode(), Response::HTTP_ACCEPTED);
    }

    /**
     * @Then I should be notified that my password has been successfully changed
     */
    public function iShouldBeNotifiedThatMyPasswordHasBeenSuccessfullyChanged(): void
    {
        Assert::same($this->client->getLastResponse()->getStatusCode(), Response::HTTP_ACCEPTED);
    }

    /**
     * @Then I should not be able to change my password again with the same token
     */
    public function iShouldNotBeAbleToChangeMyPasswordAgainWithTheSameToken(): void
    {
        $this->client->executeCustomRequest($this->request);

        $lastResponse = $this->client->getLastResponse();

        Assert::same($lastResponse->getStatusCode(), Response::HTTP_INTERNAL_SERVER_ERROR);
        $message = $this->responseChecker->getError($lastResponse);
        Assert::startsWith($message, 'No user found with reset token: ');
    }

    private function logIn(string $username, string $password): void
    {
        $this->iWantToLogIn();
        $this->iSpecifyTheUsername($username);
        $this->iSpecifyThePasswordAs($password);
        $this->iLogIn();
    }
}
