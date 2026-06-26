<x-layouts::app :title="__('Create Dataset Project')">
    <div class="mx-auto w-full max-w-2xl">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Create dataset project</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Start a secure workspace for private dataset artifacts.</p>

        <form class="mt-6 space-y-5" method="POST" action="{{ route('projects.store', $team) }}">
            @csrf

            <flux:input name="name" :label="__('Name')" :value="old('name')" required autofocus />
            <flux:textarea name="description" :label="__('Description')">{{ old('description') }}</flux:textarea>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">Create project</flux:button>
                <flux:button :href="route('projects.index', $team)" wire:navigate variant="ghost">Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
