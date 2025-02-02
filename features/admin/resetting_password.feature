@administrator_login
Feature: Resetting an administrator's password
    In order to login to my administrator account when I forget my password
    As a not logged in Administrator
    I want to be able to reset my password

    Background:
        Given there is an administrator "sylius@example.com" identified by "sylius"

    @email @api
    Scenario: Sending an administrator's password reset request
        When I want to reset password
        And I specify email as "sylius@example.com"
        And I reset it
        Then I should be notified that email with reset instruction has been sent
        And an email with instructions on how to reset the administrator's password should be sent to "sylius@example.com"

    @api
    Scenario: Changing my administrator's password
        Given I have already received a resetting password email
        When I follow the instructions to reset my password
        And I specify my new password as "newp@ssw0rd"
        And I confirm my new password as "newp@ssw0rd"
        And I reset it
        Then I should be notified that my password has been successfully changed
        And I should be able to log in as "sylius@example.com" authenticated by "newp@ssw0rd" password

    @api
    Scenario: Trying to change my administrator's password twice without sending a new password reset request
        Given I have already received an administrator's password resetting email
        When I follow the instructions to reset my password
        And I specify my new password as "newp@ssw0rd"
        And I confirm my new password as "newp@ssw0rd"
        And I reset it
        Then I should not be able to change my password again with the same token
