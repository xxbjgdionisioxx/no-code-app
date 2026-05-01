<?php

namespace Plugins;

/**
 * PluginManager — Lightweight event/hook dispatcher.
 *
 * Plugins register listeners against named hooks.
 * Platform code fires hooks at lifecycle points.
 *
 * ── Usage ────────────────────────────────────────────────────
 *
 *   // Registration (index.php):
 *   $pluginManager->register(new AuditLogPlugin($db));
 *   $pluginManager->boot();
 *
 *   // Firing a hook (RecordEngine, WorkflowEngine, etc.):
 *   $pluginManager->fire('record_created', ['record_id' => 5, 'values' => [...]]);
 *
 *   // Listener registration (inside a plugin's boot()):
 *   $manager->on('record_created', function(array $payload) { ... });
 */
class PluginManager
{
    /** @var PluginInterface[] */
    private array $plugins = [];

    /** @var array<string, callable[]> */
    private array $listeners = [];

    // ── Plugin Registration ──────────────────────────────────

    /**
     * Register a plugin instance.
     * Plugins are booted lazily via boot().
     */
    public function register(PluginInterface $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    /**
     * Boot all registered plugins — call their boot() methods.
     * Should be called once in index.php after all plugins are registered.
     */
    public function boot(): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->boot($this);
        }
    }

    /**
     * Return metadata for all registered plugins.
     * Useful for an admin "Plugins" page.
     */
    public function list(): array
    {
        return array_map(fn($p) => $p->meta(), $this->plugins);
    }

    // ── Hook/Event System ────────────────────────────────────

    /**
     * Register a listener for a named hook.
     *
     * @param string   $hook      Hook name (e.g., 'record_created')
     * @param callable $listener  Callable receiving the payload array
     * @param int      $priority  Lower numbers run first (default: 10)
     */
    public function on(string $hook, callable $listener, int $priority = 10): void
    {
        $this->listeners[$hook][] = ['callable' => $listener, 'priority' => $priority];

        // Sort listeners by priority
        usort($this->listeners[$hook], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Fire a hook — invoke all registered listeners in priority order.
     *
     * @param string $hook    Hook name
     * @param array  $payload Data passed to every listener
     */
    public function fire(string $hook, array $payload = []): void
    {
        $listeners = $this->listeners[$hook] ?? [];

        foreach ($listeners as $entry) {
            try {
                ($entry['callable'])($payload);
            } catch (\Throwable $e) {
                // Plugin errors must never crash the main application
                // Log to error_log in production; surface in dev mode
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    error_log("[AppForge Plugin Error] Hook '{$hook}': " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Check if any listeners are registered for a given hook.
     */
    public function has(string $hook): bool
    {
        return !empty($this->listeners[$hook]);
    }
}
