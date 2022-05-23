<?php

namespace Tests\Browser;

use Faker\Factory;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class WebinarHighloadTest extends DuskTestCase
{
    const LAST_USER_ID = 35;

    protected $multipleBrowsersClosure;

    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testUserLogsInAndJoinsWebinarAndSendsMessageToChat()
    {
        $this->numberOfBrowsers = 20;
        $this->prepareClosure();

        parent::setUp();
        $this->browse($this->multipleBrowsersClosure);
    }

    protected function prepareClosure()
    {
        $this->multipleBrowsersClosure = function (...$browsers) {
            $users = $this->getUsers($this->numberOfBrowsers, self::LAST_USER_ID + 1);

            foreach ($users as $key => $user) {
                $this->signupForWebinarAndPostChatMessage($browsers[$key], $user['email'], $user['password'], $user['phone'], $user['name']);
            }
        };
    }

    protected function getUsers(int $count, int $offset = 0): array
    {
        $users = [];
        $password = 'qoi73dnD37';
        for ($i = $offset; $i < $count + $offset; $i++) {
            $users[] = [
                'name' => 'fake' . $i,
                'email' => 'fake' . $i . '@edly.club',
                'phone' => '+79999999999',
                'password' => $password,
            ];
        }

        return $users;
    }

    protected function submitChatMessage(Browser $browser, string $message)
    {
        return $browser
            ->waitFor('#input_mes')
            ->type('#input_mes', $message)
            ->pause(200)
            ->click('.chat_input_send')
            ->click('.btn_chat_send')
            ->pause(2000);
    }

    protected function loginAndPostChatMessage(Browser $browser, string $email, string $password)
    {
        $messages = ['One'];

        $browser
            ->visit('https://edly.club/webinar/26/loadtest')
            ->waitForText('Регистрация')
            ->pause(2000)
            ->clickLink('Войти')
            ->waitForText('Авторизация')
            ->type('#root > div > div.MuiGrid-root.body_auth_main.MuiGrid-container.MuiGrid-justify-xs-center > div > form > input', $email)
            ->type('#root > div > div.MuiGrid-root.body_auth_main.MuiGrid-container.MuiGrid-justify-xs-center > div > form > div.input_icon > input', $password)
            ->click('.auth_btn_margin')
            ->pause(2000)
            ->waitFor('#input_mes');

        foreach ($messages as $message) {
            $this
                ->submitChatMessage($browser, $message)
                ->assertSee($message);
        }
    }

    protected function signupForWebinarAndPostChatMessage(Browser $browser, string $email, string $password, string $phone, $name)
    {
        $messages = ['One'];

        $browser
            ->visit('http://dev.edly.club/webinar/108/loadtest')
            ->waitForText('loadtest')
            ->type('#root > div > div.reg_from_in_webinar > div > div > div.reg_in_webinar_form > div.reg_in_webinar_form_deck > form > div:nth-child(1) > div:nth-child(1) > input', $email)
            ->type('#root > div > div.reg_from_in_webinar > div > div > div.reg_in_webinar_form > div.reg_in_webinar_form_deck > form > div:nth-child(2) > div:nth-child(1) > input', $phone)
            ->check('#root > div > div.reg_from_in_webinar > div > div > div.reg_in_webinar_form > div.reg_in_webinar_form_deck > form > div.deck_checkbox > div:nth-child(1) > div.checkbox_ui')
            ->check('#root > div > div.reg_from_in_webinar > div > div > div.reg_in_webinar_form > div.reg_in_webinar_form_deck > form > div.deck_checkbox > div:nth-child(2) > div.checkbox_ui')
            ->pause(200)
            ->press('Записаться')
            ->waitForText('Авторизация')
            ->type('#root > div > div.MuiGrid-root.MuiGrid-container.MuiGrid-justify-xs-center > div > form > div.input_ui_main.registration_finish_form_input_options_register > div:nth-child(1) > input', $name)
            ->press('Войти')
            ->waitFor('#input_mes');

        foreach ($messages as $message) {
            $this
                ->submitChatMessage($browser, $message)
                ->assertSee($message);
        }
    }

    public function _testUserJoinsWebinarAndSendsMessageToChat()
    {
        $faker = Factory::create();
        $this->browse(function (Browser $browser) use ($faker) {
            $browser
                ->visit('https://edly.club/webinar/HL')
                ->waitForText('Регистрация')
                ->waitUntil('document.readyState == "complete"')
                ->type('firstName', 'Fake')
                ->type('lastName', 'User 01')
                ->type('phone', '+79011111111')
                ->type('email', 'fake01@edly.club')
                ->type('password', '123123123')
                ->check('#root > div > div.MuiGrid-root.MuiGrid-container.MuiGrid-justify-xs-center > div > form > div:nth-child(8) > div.reg_checkbox > span > span.MuiIconButton-label > input')
                ->check('#root > div > div.MuiGrid-root.MuiGrid-container.MuiGrid-justify-xs-center > div > form > div:nth-child(9) > div.reg_checkbox > span > span.MuiIconButton-label > input')
                ->click('.register_button_option')
                ->waitForLocation('/webinar/HL')
                ->pause(2000)
                ->waitFor('#input_mes')
                ->type('#input_mes', 'Hello!')
                ->pause(200)
                ->click('.chat_input_send')
                ->click('.btn_chat_send')
                ->pause(2000)
                ->assertSee('Hello!');
        });
    }
}
