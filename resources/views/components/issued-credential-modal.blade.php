@props([
    'credential' => null,
])

<flux:modal wire:model="showIssuedCredentialModal" @close="closeIssuedCredentialModal" class="max-w-lg w-full">
    @if ($credential)
        <div
            wire:key="issued-credential-{{ md5(($credential['identifier'] ?? '').'|'.($credential['temporary_password'] ?? '')) }}"
            class="space-y-5"
            x-data="{
                copied: false,
                copyTimer: null,
                async copyPassword() {
                    const text = this.$refs.tempPassword?.innerText?.trim() ?? '';
                    if (! text) {
                        return;
                    }

                    let succeeded = false;

                    if (navigator.clipboard && window.isSecureContext) {
                        try {
                            await navigator.clipboard.writeText(text);
                            succeeded = true;
                        } catch (error) {
                            succeeded = false;
                        }
                    }

                    if (! succeeded) {
                        const helper = document.createElement('textarea');
                        helper.value = text;
                        helper.setAttribute('readonly', '');
                        helper.style.position = 'fixed';
                        helper.style.opacity = '0';
                        document.body.appendChild(helper);
                        helper.select();
                        helper.setSelectionRange(0, helper.value.length);

                        try {
                            succeeded = document.execCommand('copy');
                        } catch (error) {
                            succeeded = false;
                        }

                        document.body.removeChild(helper);
                    }

                    if (! succeeded) {
                        const range = document.createRange();
                        range.selectNodeContents(this.$refs.tempPassword);
                        const selection = window.getSelection();
                        selection?.removeAllRanges();
                        selection?.addRange(range);

                        return;
                    }

                    this.copied = true;
                    clearTimeout(this.copyTimer);
                    this.copyTimer = setTimeout(() => {
                        this.copied = false;
                    }, 2000);
                }
            }"
        >
            <div>
                <flux:heading size="lg">{{ $credential['heading'] }}</flux:heading>
                <flux:subheading>
                    {{ __('Salin dan bagikan password sementara ini kepada pengguna. Password ini hanya ditampilkan satu kali.') }}
                </flux:subheading>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 space-y-3 text-sm dark:border-zinc-700/70 dark:bg-zinc-900/60">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Nama') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $credential['name'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Email / ID Masuk') }}</div>
                        <div class="font-medium text-zinc-900 dark:text-zinc-100 break-all">{{ $credential['identifier'] }}</div>
                    </div>
                </div>

                @if ($credential['identifier'] !== $credential['email'])
                    <div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Email Akun') }}</div>
                        <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300 break-all">{{ $credential['email'] }}</div>
                    </div>
                @endif

                <flux:separator variant="subtle" />

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Password Sementara') }}</div>
                        <div x-ref="tempPassword" class="mt-0.5 font-mono text-base font-semibold tracking-wide text-zinc-900 dark:text-zinc-100 select-all break-all">
                            {{ $credential['temporary_password'] }}
                        </div>
                    </div>
                    <flux:button
                        type="button"
                        variant="outline"
                        icon="clipboard-document"
                        class="w-full shrink-0 sm:w-auto"
                        x-on:click="copyPassword()"
                    >
                        <span x-show="!copied">{{ __('Salin') }}</span>
                        <span x-show="copied" x-cloak>{{ __('Tersalin') }}</span>
                    </flux:button>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:button type="button" variant="primary" wire:click="closeIssuedCredentialModal" class="w-full sm:w-auto">
                    {{ __('Tutup') }}
                </flux:button>
            </div>
        </div>
    @endif
</flux:modal>

