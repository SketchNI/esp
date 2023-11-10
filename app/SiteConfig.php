<?php

namespace App;

class SiteConfig
{
    /**
     * @var string
     */
    protected string $framework;

    /**
     * @var array
     */
    protected array $options;

    /**
     * @var string
     */
    protected string $database;

    /**
     * @var bool
     */
    protected bool $ssl;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * @var string
     */
    protected string $url;

    /**
     * @var string
     */
    protected string $username;

    /**
     * @var string
     */
    protected string $current_dir;

    /**
     * @var string
     */
    protected string $project_name;

    /**
     * @var string
     */
    protected string $project_directory;

    /**
     * @return string
     */
    public function getFramework(): string
    {
        return $this->framework;
    }

    /**
     * @param  string  $framework
     *
     * @return SiteConfig
     */
    public function setFramework(string $framework): SiteConfig
    {
        $this->framework = strip_newlines($framework);
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     *
     * @return SiteConfig
     */
    public function setOptions(array $options): SiteConfig
    {
        $this->options = strip_newlines($options);
        return $this;
    }

    /**
     * @return bool
     */
    public function isSsl(): bool
    {
        return $this->ssl;
    }

    /**
     * @param  bool  $ssl
     *
     * @return SiteConfig
     */
    public function setSsl(bool $ssl): SiteConfig
    {
        $this->ssl = $ssl;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     *
     * @return SiteConfig
     */
    public function setName(string $name): SiteConfig
    {
        $this->name = strip_newlines($name);
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return SiteConfig
     */
    public function setDomain(): SiteConfig
    {
        $this->domain = strip_newlines(sprintf('%s.%s',
            $this->name,
            file_get_contents(sprintf("%s/.esp/tld", getenv('HOME')))
        ));
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return SiteConfig
     */
    public function setUrl(): SiteConfig
    {
        $this->url = sprintf('%s://%s', $this->ssl ? 'https' : 'http', $this->domain);
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return SiteConfig
     */
    public function setUsername(): SiteConfig
    {
        $this->username = strip_newlines(posix_getpwuid(posix_geteuid())['name']);
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentDir(): string
    {
        return $this->current_dir;
    }

    /**
     * @return SiteConfig
     */
    public function setCurrentDir(): SiteConfig
    {
        $this->current_dir = strip_newlines(posix_getcwd());
        return $this;
    }

    /**
     * @return string
     */
    public function getProjectName(): string
    {
        return $this->project_name;
    }

    /**
     * @return SiteConfig
     */
    public function setProjectName(): SiteConfig
    {
        $this->project_name = strip_newlines(posix_compat_name($this->name));
        return $this;
    }

    /**
     * @return string
     */
    public function getProjectDirectory(): string
    {
        return $this->project_directory;
    }

    /**
     * @return SiteConfig
     */
    public function setProjectDirectory(): SiteConfig
    {
        $this->project_directory = strip_newlines($this->getCurrentDir());
        return $this;
    }

}
