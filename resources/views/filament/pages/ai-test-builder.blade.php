<x-filament-panels::page>

    <style>
        .ai-chat-container{display:flex;gap:1rem;min-height:70vh}
        .ai-sidebar{width:260px;flex-shrink:0}
        .ai-main{flex:1;display:flex;flex-direction:column;min-width:0}
        .ai-code-panel{width:380px;flex-shrink:0;transition:width .2s ease}
        .chat-messages{flex:1;overflow-y:auto;max-height:55vh;padding:1rem;scroll-behavior:smooth}
        .chat-bubble{max-width:85%;padding:.75rem 1rem;border-radius:.75rem;margin-bottom:.75rem;word-wrap:break-word;white-space:pre-wrap;font-size:.875rem;line-height:1.5}
        .chat-bubble-user{margin-left:auto;background:#3b82f6;color:#fff;border-bottom-right-radius:.25rem}
        .chat-bubble-assistant{margin-right:auto;background:#f3f4f6;color:#1f2937;border-bottom-left-radius:.25rem}
        .dark .chat-bubble-assistant{background:#374151;color:#e5e7eb}
        .chat-bubble pre{background:rgba(0,0,0,.1);border-radius:.375rem;padding:.5rem;overflow-x:auto;margin:.5rem 0;font-size:.8rem}
        .dark .chat-bubble pre{background:rgba(255,255,255,.1)}
        .chat-timestamp{font-size:.65rem;margin-top:.375rem;opacity:.55}
        .chat-bubble-user .chat-timestamp{text-align:right}
        .chat-bubble-assistant .chat-timestamp{text-align:left}
        .chat-input-area{border-top:1px solid #e5e7eb;padding:.75rem}
        .dark .chat-input-area{border-color:#374151}
        .file-tab{cursor:pointer;padding:.375rem .75rem;border-radius:.375rem .375rem 0 0;font-size:.75rem;font-family:monospace;transition:background .15s}
        .file-tab.active{background:#3b82f6;color:#fff}
        .file-tab:not(.active){background:#e5e7eb;color:#6b7280}
        .dark .file-tab:not(.active){background:#374151;color:#9ca3af}
        .code-display{background:#1e1e1e;color:#d4d4d4;font-family:monospace;font-size:.8rem;padding:1rem;overflow:auto;max-height:55vh;border-radius:0 0 .5rem .5rem;white-space:pre;line-height:1.6}
        .conversation-item{cursor:pointer;padding:.5rem .75rem;border-radius:.375rem;font-size:.8125rem;transition:background .15s;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .conversation-item:hover{background:#e5e7eb}
        .dark .conversation-item:hover{background:#374151}
        .conversation-item.active{background:#dbeafe;color:#1d4ed8}
        .dark .conversation-item.active{background:#1e3a8a;color:#93c5fd}
        .framework-toggle{display:inline-flex;border-radius:.375rem;overflow:hidden;border:1px solid #d1d5db}
        .dark .framework-toggle{border-color:#4b5563}
        .framework-toggle button{padding:.25rem .75rem;font-size:.75rem;font-weight:500;transition:all .15s;border:none;cursor:pointer}
        .framework-toggle button.active{background:#3b82f6;color:#fff}
        .framework-toggle button:not(.active){background:transparent;color:#6b7280}
        .dark .framework-toggle button:not(.active){color:#9ca3af}
        .crawl-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.125rem .5rem;border-radius:9999px;font-size:.6875rem;font-weight:500;background:#dcfce7;color:#166534}
        .dark .crawl-badge{background:#052e16;color:#86efac}
        .loading-dot{animation:loadingPulse 1.4s infinite both}
        .loading-dot:nth-child(2){animation-delay:.2s}
        .loading-dot:nth-child(3){animation-delay:.4s}
        @keyframes loadingPulse{0%,80%,100%{opacity:.3}40%{opacity:1}}
        @media(max-width:1024px){
            .ai-sidebar{display:none}
            .ai-code-panel{display:none}
        }
    </style>

    <div class="ai-chat-container" x-data="{ showCodePanel: true }">

        {{-- ═══ Sidebar ═══ --}}
        <div class="ai-sidebar">
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4 h-full flex flex-col gap-4">

                {{-- Project selector --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Project</label>
                    <select
                        wire:model.live="projectId"
                        wire:change="selectProject($event.target.value)"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="">Select a project...</option>
                        @foreach($this->projects as $project)
                            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if($projectId)
                    {{-- New conversation button --}}
                    <button
                        wire:click="newConversation"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 transition"
                    >
                        <x-heroicon-s-plus class="w-4 h-4" />
                        New Chat
                    </button>

                    {{-- Conversation history --}}
                    <div class="flex-1 overflow-y-auto">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Recent</p>
                        @forelse($this->conversations as $conv)
                            <div
                                wire:click="loadConversation('{{ $conv['ulid'] }}')"
                                @class([
                                    'conversation-item mb-1',
                                    'active' => $conversationUlid === $conv['ulid'],
                                ])
                                title="{{ $conv['title'] }}"
                            >
                                {{ $conv['title'] }}
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 dark:text-gray-500 italic">No conversations yet</p>
                        @endforelse
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center">
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic text-center">Select a project to start</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══ Main Chat Panel ═══ --}}
        <div class="ai-main">
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col h-full">

                @if(!$projectId)
                    {{-- No project selected — full-panel prompt --}}
                    <div class="flex-1 flex flex-col items-center justify-center text-center px-8">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <x-heroicon-o-folder-open class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Select a Project</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                            Choose a project from the sidebar to start building AI-generated tests.
                        </p>
                    </div>
                @else
                    {{-- Header bar --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                @if($conversationUlid)
                                    {{ $this->getConversation()?->title ?? 'Chat' }}
                                @else
                                    New Conversation
                                @endif
                            </h3>

                            @if($this->getConversation()?->crawl_data)
                                <span class="crawl-badge">
                                    <x-heroicon-s-globe-alt class="w-3 h-3" />
                                    Crawled
                                </span>
                            @elseif($this->getProjectCrawlUrl())
                                <span class="crawl-badge" title="Using project sitemap: {{ $this->getProjectCrawlUrl() }}">
                                    <x-heroicon-s-globe-alt class="w-3 h-3" />
                                    Sitemap
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Code panel toggle (when files exist but panel is hidden) --}}
                            @if(!empty($generatedFiles))
                                <button
                                    x-show="!showCodePanel"
                                    x-cloak
                                    @click="showCodePanel = true"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900 transition"
                                >
                                    <x-heroicon-s-code-bracket class="w-3.5 h-3.5" />
                                    Show Code
                                </button>
                            @endif

                            {{-- Framework toggle --}}
                            <div class="framework-toggle">
                                <button
                                    wire:click="switchFramework('cypress')"
                                    @class(['active' => $framework === 'cypress'])
                                >Cypress</button>
                                <button
                                    wire:click="switchFramework('playwright')"
                                    @class(['active' => $framework === 'playwright'])
                                >Playwright</button>
                            </div>
                        </div>
                    </div>

                    {{-- Chat messages area --}}
                    <div class="chat-messages" id="chat-messages">
                        @if(empty($chatMessages) && !$conversationUlid)
                            {{-- Empty state --}}
                            <div class="flex flex-col items-center justify-center h-full text-center px-8">
                                <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-950 flex items-center justify-center mb-4">
                                    <x-heroicon-o-sparkles class="w-8 h-8 text-primary-500" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">AI Test Builder</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mb-6">
                                    Describe the tests you need in plain English.
                                    @if($this->getProjectCrawlUrl())
                                        This project has a saved sitemap from <strong>{{ $this->getProjectCrawlUrl() }}</strong> that will be used automatically.
                                    @else
                                        Optionally crawl your site first for real selectors and page structure.
                                    @endif
                                </p>

                                {{-- Crawl input --}}
                                <div class="w-full max-w-md">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block text-left">
                                        {{ $this->getProjectCrawlUrl() ? 'Re-crawl to update sitemap' : 'Crawl a page first (optional)' }}
                                    </label>
                                    <div class="flex gap-2">
                                        <input
                                            type="url"
                                            wire:model="crawlUrl"
                                            placeholder="https://example.com"
                                            class="flex-1 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                        />
                                        <button
                                            wire:click="crawlSite"
                                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-500 transition disabled:opacity-50"
                                        >
                                            <span wire:loading.remove wire:target="crawlSite">Crawl</span>
                                            <span wire:loading wire:target="crawlSite" class="flex items-center gap-1">
                                                <x-heroicon-s-arrow-path class="w-4 h-4 animate-spin" />
                                                Crawling...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Message history --}}
                            @foreach($chatMessages as $msg)
                                <div @class([
                                    'chat-bubble',
                                    'chat-bubble-user' => ($msg['role'] ?? '') === 'user',
                                    'chat-bubble-assistant' => ($msg['role'] ?? '') === 'assistant',
                                ])>
                                    {!! nl2br(e($msg['content'] ?? '')) !!}
                                    @if(!empty($msg['timestamp']))
                                        <div class="chat-timestamp">
                                            {{ \Carbon\Carbon::parse($msg['timestamp'])->format('M j, g:ia') }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            {{-- Animated loading bubble (client-side, shows instantly) --}}
                            <div
                                wire:loading
                                wire:target="sendMessage"
                                class="chat-bubble chat-bubble-assistant"
                                x-data="aiLoadingMessages()"
                                x-init="start()"
                                x-on:livewire-upload-progress.window="stop()"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="flex gap-0.5">
                                        <span class="loading-dot w-1.5 h-1.5 rounded-full bg-primary-500 inline-block"></span>
                                        <span class="loading-dot w-1.5 h-1.5 rounded-full bg-primary-500 inline-block"></span>
                                        <span class="loading-dot w-1.5 h-1.5 rounded-full bg-primary-500 inline-block"></span>
                                    </span>
                                    <span class="text-gray-500 dark:text-gray-400" x-text="message"></span>
                                </div>
                            </div>

                            {{-- Crawl loading indicator --}}
                            <div
                                wire:loading
                                wire:target="crawlSite"
                                class="chat-bubble chat-bubble-assistant"
                            >
                                <div class="flex items-center gap-2">
                                    <x-heroicon-s-arrow-path class="w-4 h-4 animate-spin text-emerald-500" />
                                    <span class="text-gray-500 dark:text-gray-400">Crawling site for selectors and page structure...</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Chat input --}}
                    <div class="chat-input-area">
                        @if(!empty($chatMessages) || $conversationUlid)
                            {{-- Crawl URL input (compact, for existing conversations without crawl data) --}}
                            @if(!$this->getConversation()?->crawl_data && !$this->getProjectCrawlUrl())
                                <div class="flex gap-2 mb-2">
                                    <input
                                        type="url"
                                        wire:model="crawlUrl"
                                        placeholder="Crawl a URL for real selectors..."
                                        class="flex-1 text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 py-1.5"
                                    />
                                    <button
                                        wire:click="crawlSite"
                                        class="px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900 transition disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="crawlSite">Crawl</span>
                                        <span wire:loading wire:target="crawlSite">Crawling...</span>
                                    </button>
                                </div>
                            @endif
                        @endif

                        <form wire:submit="sendMessage" class="flex gap-2">
                            <input
                                type="text"
                                wire:model="userMessage"
                                placeholder="{{ empty($chatMessages) ? 'Describe the tests you want to generate...' : 'Ask for changes or additional tests...' }}"
                                class="flex-1 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                @disabled($isGenerating)
                                autofocus
                            />
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 transition disabled:opacity-50"
                                @disabled($isGenerating || !$projectId)
                            >
                                <span wire:loading.remove wire:target="sendMessage">
                                    <x-heroicon-s-paper-airplane class="w-4 h-4" />
                                </span>
                                <span wire:loading wire:target="sendMessage">
                                    <x-heroicon-s-arrow-path class="w-4 h-4 animate-spin" />
                                </span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══ Code Preview Panel (collapsible) ═══ --}}
        @if(!empty($generatedFiles))
            <div class="ai-code-panel" x-show="showCodePanel" x-transition.opacity>
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col h-full">

                    {{-- Code panel header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Generated Files</h3>
                        <div class="flex items-center gap-1">
                            <button
                                wire:click="downloadZip"
                                class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                                title="Download ZIP"
                            >
                                <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                            </button>
                            <button
                                @click="showCodePanel = false"
                                class="p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                                title="Close panel"
                            >
                                <x-heroicon-s-x-mark class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div x-data="{ activeFile: '{{ array_key_first($generatedFiles) }}' }" class="flex flex-col flex-1 overflow-hidden">
                        {{-- File tabs --}}
                        <div class="flex flex-wrap gap-0 px-4 pt-3 overflow-x-auto">
                            @foreach($generatedFiles as $path => $content)
                                <button
                                    class="file-tab"
                                    :class="{ 'active': activeFile === '{{ $path }}' }"
                                    @click="activeFile = '{{ $path }}'"
                                >
                                    {{ basename($path) }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Code display --}}
                        <div class="flex-1 overflow-hidden px-4 pb-4">
                            @foreach($generatedFiles as $path => $content)
                                <div x-show="activeFile === '{{ $path }}'" class="code-display">{{ $content }}</div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Save as suite --}}
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        @if($this->linkedSuite)
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Linked to <span class="font-medium text-gray-700 dark:text-gray-300">{{ $this->linkedSuite->name }}</span>
                                </div>
                                <button
                                    wire:click="saveAsSuite"
                                    class="px-3 py-2 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-500 transition"
                                >
                                    <span wire:loading.remove wire:target="saveAsSuite">Update Suite</span>
                                    <span wire:loading wire:target="saveAsSuite">Updating...</span>
                                </button>
                            </div>
                        @else
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Save as managed suite</label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    wire:model="suiteName"
                                    placeholder="Suite name..."
                                    class="flex-1 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                />
                                <button
                                    wire:click="saveAsSuite"
                                    class="px-3 py-2 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-500 transition"
                                >
                                    Save
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    </div>

    <script>
        function aiLoadingMessages() {
            const messages = [
                'Thinking...',
                'Analysing your request...',
                'Generating test code...',
                'Building test structure...',
                'Crafting assertions...',
                'Almost there...',
            ];
            return {
                message: messages[0],
                _interval: null,
                _idx: 0,
                start() {
                    this._interval = setInterval(() => {
                        this._idx = (this._idx + 1) % messages.length;
                        this.message = messages[this._idx];
                    }, 3000);
                },
                stop() {
                    clearInterval(this._interval);
                },
                destroy() {
                    clearInterval(this._interval);
                }
            };
        }

        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', ({el}) => {
                if (el.id === 'chat-messages') {
                    el.scrollTop = el.scrollHeight;
                }
            });

            Livewire.on('scroll-chat', () => {
                setTimeout(() => {
                    const el = document.getElementById('chat-messages');
                    if (el) el.scrollTop = el.scrollHeight;
                }, 50);
            });

            // Scroll to bottom on initial page load if messages exist
            requestAnimationFrame(() => {
                const el = document.getElementById('chat-messages');
                if (el && el.children.length > 1) el.scrollTop = el.scrollHeight;
            });
        });
    </script>

</x-filament-panels::page>
