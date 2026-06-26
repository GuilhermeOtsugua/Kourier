<x-layouts::app :title="$project->name">
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <div>
            <a class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" href="{{ route('projects.index', $team) }}" wire:navigate>&larr; Dataset projects</a>
            <h1 class="mt-3 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $project->name }}</h1>
            @if ($project->description)
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $project->description }}</p>
            @endif
        </div>

        <form class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900" method="POST" action="{{ route('artifacts.store', [$team, $project]) }}" enctype="multipart/form-data">
            @csrf

            <div class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="flex-1">
                    <flux:input type="file" name="artifact" :label="__('Upload private artifact')" required />
                </div>
                <flux:button type="submit" variant="primary">Upload artifact</flux:button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            @forelse ($project->artifacts as $artifact)
                <div class="border-b border-zinc-200 p-4 last:border-b-0 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $artifact->original_filename }}</div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $artifact->processing_status }} · {{ $artifact->review_status }} · {{ number_format($artifact->size_bytes) }} bytes
                            </div>
                        </div>
                        <flux:button :href="route('artifacts.download', [$team, $project, $artifact])" variant="ghost">Download</flux:button>
                    </div>

                    @if ($artifact->labels->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($artifact->labels as $label)
                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $label->key }}: {{ $label->value }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <form class="mt-4 grid gap-3 md:grid-cols-4" method="POST" action="{{ route('artifact-labels.store', [$team, $project, $artifact]) }}">
                        @csrf
                        <flux:input name="key" :label="__('Label')" placeholder="discipline" />
                        <flux:input name="value" :label="__('Value')" placeholder="dressage" />
                        <label class="flex flex-col gap-2 text-sm font-medium text-zinc-800 dark:text-zinc-200">
                            Review
                            <select class="rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" name="review_status">
                                <option value="pending" @selected($artifact->review_status === 'pending')>Pending</option>
                                <option value="approved" @selected($artifact->review_status === 'approved')>Approved</option>
                                <option value="rejected" @selected($artifact->review_status === 'rejected')>Rejected</option>
                            </select>
                        </label>
                        <div class="flex items-end">
                            <flux:button type="submit" variant="primary">Save label</flux:button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="p-6 text-sm text-zinc-600 dark:text-zinc-400">No artifacts uploaded yet.</div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
