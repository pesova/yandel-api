<?php

namespace App\Services\Payment;

use App\Exceptions\PaymentException;
use App\Contracts\PaymentDriverInterface;
use App\Contracts\PaymentGatewayInterface;

class PaymentGatewayManager implements PaymentGatewayInterface
{
    /**
     * Payment Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Payment Driver Settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * Payment Driver Name.
     *
     * @var string
     */
    protected $driver;

    /**
     * Payment Driver Instance.
     *
     * @var object
     */
    protected $driverInstance;

    /**
     * PaymentGatewayManager constructor.
     *
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct( array $config = null )
    {
        $this->config = $config ?? $this->loadDefaultConfig();
        $this->use($this->config['default']);
        $this->driverInstance = $this->getFreshDriverInstance();
    }

    /**
     * Set custom configs
     * we can use this method when we want to use dynamic configs
     *
     * @param $key
     * @param $value|null
     *
     * @return $this
     */
    public function config($key, $value = null)
    {
        $configs = [];

        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $configs[$k] = $v;
        }

        $this->settings = array_merge($this->settings, $configs);

        return $this;
    }

    /**
     * Retrieve default config.
     *
     * @return array
     */
    protected function loadDefaultConfig() : array
    {
        return config('custom.payment');
    }

    /**
     * Retrieve current driver instance or generate new one.
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getDriverInstance()
    {
        if (!empty($this->driverInstance)) {
            return $this->driverInstance;
        }

        return $this->getFreshDriverInstance();
    }

    /**
     * Get new driver instance
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getFreshDriverInstance()
    {
        $this->validateDriver();
        $class = $this->config['map'][$this->driver];

        return new $class($this->settings);
    }

    /**
     * Validate driver.
     *
     * @throws \Exception
     */
    protected function validateDriver()
    {
        if (empty($this->driver)) {
            throw new DriverNotFoundException('Driver not selected or default driver does not exist.');
        }

        if (empty($this->config['drivers'][$this->driver]) || empty($this->config['map'][$this->driver])) {
            throw new DriverNotFoundException('Driver not found or driver map missing in config file.');
        }

        if (!class_exists($this->config['map'][$this->driver])) {
            throw new DriverNotFoundException('Driver source not found. Please update the driver map.');
        }

        $reflect = new \ReflectionClass($this->config['map'][$this->driver]);

        if (!$reflect->implementsInterface(PaymentDriverInterface::class)) {
            throw new PaymentException("Driver must be an instance of PaymentDriverInterface.", 500);
        }
    }

    /**
     * Change the driver on the fly.
     *
     * @param $driver
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function use($driver)
    {
        $this->driver = $driver;
        $this->validateDriver();
        $this->settings = $this->config['drivers'][$driver];

        return $this;
    }

    /**
     * Dynamically proxy method calls to the payment driver.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driverInstance->{$method}(...$parameters);
    }
}