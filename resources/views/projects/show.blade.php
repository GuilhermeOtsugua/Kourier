<x-layouts::app :title="$project->name">
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <div>
            <a class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" href="{{ route('projects.index', $team) }}" wire:navigate>&larr; Dataset projects</a>
            <h1 class="mt-3 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $project->name }}</h1>
            @if ($project->description)
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $project->description }}</p>
            @endif
        </div>

        <div class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
            Artifact intake will appear here.
        </div>
    </div>
</x-layouts::app>
