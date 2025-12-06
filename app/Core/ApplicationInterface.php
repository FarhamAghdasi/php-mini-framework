<?php
namespace App\Core;

interface ApplicationInterface
{
    public static function getInstance();
    public function setBasePath(string $path): void;
    public function getBasePath(): string;
    public function loadConfig(array $config): void;
    public function getConfig(?string $key = null);
    public function initServices(): void;
    public function getService(string $name);
    public function setRouter(Router $router): void;
    public function getRouter(): ?Router;
    public function getRequest(): ?Request;
    public function getResponse(): ?Response;
    public function getSession(): ?Session;
    public function run(): void;
}