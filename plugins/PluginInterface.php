<?php

namespace Plugins;

/**
 * PluginInterface — Contract every AppForge plugin must implement.
 *
 * Plugins integrate into the platform lifecycle via hooks/events.
 * The boot() method is called once during application startup.
 *
 * Example hooks fired by the platform:
 *   - before_dispatch    (route about to be dispatched)
 *   - record_created     (after a record is inserted)
 *   - record_updated     (after a record is updated)
 *   - record_deleted     (after a record is deleted)
 *
 * ── How to create a plugin ──────────────────────────────────
 *
 *   1. Create a class implementing PluginInterface in plugins/myplugin/
 *   2. Register it in index.php:
 *        $pluginManager->register(new MyPlugin($db));
 *   3. In boot(), call $this->manager->on('hook_name', callable)
 *
 * ── Example custom field type registered from a plugin ──────
 *
 *   public function boot(): void {
 *       $this->manager->on('record_created', function(array $payload) {
 *           // Do something with $payload['record_id'], $payload['values']
 *       });
 *   }
 */
interface PluginInterface
{
    /**
     * Plugin metadata — returned as an associative array.
     * Required keys: name, version, description, author
     */
    public function meta(): array;

    /**
     * Boot the plugin — register hooks, initialize resources.
     * Called once during application startup by PluginManager.
     */
    public function boot(PluginManager $manager): void;
}
