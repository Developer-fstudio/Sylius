@administrator_login
Feature: Resetting an administrator's password validation
    In order to avoid making mistakes when resetting administrator's password
    As a not logged in Administrator
    I want to be prevented from making mistakes in my address email

    Background:
        Given there is an administrator "sylius@example.com" identified by "sylius"

    @todo
    Scenario: Trying to reset my administrator's password without specifying email
        When I want to reset password
        And I do not specify email
        And I try to reset it
        Then I should be notified that the email is required

    @todo
    Scenario: Trying to reset my administrator's password with a wrong confirmation password
        Given I have already received an administrator's password resetting email
        When I follow link on my email to reset my password
        And I specify my new password as "newp@ssw0rd"
        And I confirm my new password as "wrongp@ssw0rd"
        And I try to reset it
        Then I should be notified that the entered passwords do not match

    @todo
    Scenario: Trying to reset my administrator's password with a too short password
        Given I have already received an administrator's password resetting email
        When I follow link on my email to reset my password
        And I specify my new password as "fu"
        And I confirm my new password as "fu"
        And I try to reset it
        Then I should be notified that the password should be at least 4 characters long
