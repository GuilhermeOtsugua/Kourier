<x-layouts::app :title="__('Dashboard')">
    <livewire:pages::teams.pending-invitations-modal />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Koúrier dashboard</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Manage private dataset projects, artifacts, labels, and exports for {{ auth()->user()->currentTeam->name }}.</p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-medium text-zinc-900 dark:text-white">Dataset projects</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Create a project to upload artifacts, review labels, and request export packages.</p>
            <div class="mt-5">
                <flux:button :href="route('projects.index', auth()->user()->currentTeam)" wire:navigate variant="primary">Open projects</flux:button>
            </div>
        </div>
    </div>
</x-layouts::app>
