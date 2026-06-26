<x-layouts::app :title="__('Dataset Projects')">
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Dataset projects</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Secure workspaces for private dataset artifacts.</p>
            </div>

            <flux:button :href="route('projects.create', $team)" wire:navigate variant="primary">New project</flux:button>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            @forelse ($projects as $project)
                <a class="block border-b border-zinc-200 p-4 last:border-b-0 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800" href="{{ route('projects.show', [$team, $project]) }}" wire:navigate>
                    <div class="font-medium text-zinc-900 dark:text-white">{{ $project->name }}</div>
                    @if ($project->description)
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $project->description }}</div>
                    @endif
                </a>
            @empty
                <div class="p-6 text-sm text-zinc-600 dark:text-zinc-400">No dataset projects yet.</div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
