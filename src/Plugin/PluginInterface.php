<?php

namespace Fusion\Plugin;

/**
 * Plugin Interface
 */
interface PluginInterface
{
    /**
     * Get plugin name
     */
    public function getName(): string;

    /**
     * Get plugin version
     */
    public function getVersion(): string;

    /**
     * Get plugin description
     */
    public function getDescription(): string;

    /**
     * Install plugin
     */
    public function install(): bool;

    /**
     * Uninstall plugin
     */
    public function uninstall(): bool;

    /**
     * Activate plugin
     */
    public function activate(): bool;

    /**
     * Deactivate plugin
     */
    public function deactivate(): bool;

    /**
     * Get plugin dependencies
     */
    public function getDependencies(): array;

    /**
     * Check if plugin is compatible
     */
    public function isCompatible(string $frameworkVersion): bool;
}
