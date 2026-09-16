<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use Tests\Concerns\CleansAuthorisationFixtures;
use Tests\Concerns\CreatesPasien;
use Tests\Concerns\CreatesPetugas;

abstract class DuskTestCase extends BaseTestCase
{
    use CleansAuthorisationFixtures;
    use CreatesApplication;
    use CreatesPasien;
    use CreatesPetugas;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * A previous run that died mid-test can leave rows behind. Start clean
     * rather than inheriting them - mirrors Tests\TestCase, since Dusk tests
     * get no transactional rollback either (a real browser talks to a
     * separately-running server process over real HTTP requests).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteAuthorisationFixtures();
        $this->deletePasienFixtures();
        $this->deletePetugasFixtures();
    }

    protected function tearDown(): void
    {
        $this->deleteAuthorisationFixtures();
        $this->deletePasienFixtures();
        $this->deletePetugasFixtures();

        parent::tearDown();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the browser window should start maximized.
     *
     * @return bool
     */
    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) ||
               isset($_ENV['DUSK_START_MAXIMIZED']);
    }
}
