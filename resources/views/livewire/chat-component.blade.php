<div x-data="chatHandler()" class="livewire-chat-wrapper {{ auth()->user()->dark_mode ? 'dark-mode' : '' }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; background: {{ auth()->user()->dark_mode ? '#0b141a' : '#f0f2f5' }}; overflow: hidden; font-family: 'Outfit', sans-serif;">
    <audio id="chat-notification" src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" preload="auto"></audio>
    <audio id="sent-notification" src="https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3" preload="auto"></audio>
    
    {{-- Sidebar (Left) --}}
    <div class="sidebar" style="flex: 0 0 400px; border-right: 1px solid #d1d7db; background: white; display: flex; flex-direction: column; height: 100%; z-index: 20; position: relative;">
        
        {{-- Profile Header --}}
        <div style="height: 64px; background: #f0f2f5; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; flex-shrink: 0; border-bottom: 1px solid #e9edef;">
            <div class="relative group" @click="toggleSettings()" style="cursor: pointer;">
                <img class="w-10 h-10 rounded-full hover:brightness-95 transition-all border border-black/5" src="{{ auth()->user()->profile_photo_url }}">
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-[#f0f2f5] rounded-full"></span>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; color: #54656f; font-size: 1.15rem;">
                <div class="relative cursor-pointer hover:text-[#00a884] transition-colors" wire:click="$set('showStories', true)" title="Status">
                    <i class="fa-solid fa-circle-notch"></i>
                    @if($this->stories->count() > 0)
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-[#25d366] rounded-full border-2 border-[#f0f2f5]"></span>
                    @endif
                </div>
                <div class="relative cursor-pointer hover:text-[#00a884] transition-colors" wire:click="$set('isCreatingGroup', true)" title="New Chat">
                    <i class="fa-solid fa-message"></i>
                    @if($this->total_unread_messages > 0)
                        <span class="absolute -top-2 -right-2 bg-[#25d366] text-white text-[9px] font-black px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                            {{ $this->total_unread_messages > 99 ? '99+' : $this->total_unread_messages }}
                        </span>
                    @endif
                </div>
                <div class="relative" x-data="{ open: false }">
                    <i class="fa-solid fa-ellipsis-vertical cursor-pointer hover:text-[#00a884] p-2 transition-colors" @click="open = !open"></i>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         @click.away="open = false" x-cloak 
                         class="absolute right-0 top-full mt-2 w-52 bg-white shadow-2xl rounded-xl py-2 border border-black/5 z-50 origin-top-right">
                        <div class="px-5 py-3 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center" wire:click="$set('isCreatingGroup', true)" @click="open = false">
                             <i class="fa-solid fa-users mr-3 text-gray-400 w-5"></i> New group
                        </div>
                        <div class="px-5 py-3 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center" wire:click="toggleStarred" @click="open = false">
                             <i class="fa-solid fa-star mr-3 text-gray-400 w-5"></i> Starred messages
                        </div>
                        <div class="px-5 py-3 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center" wire:click="toggleSettings" @click="open = false">
                             <i class="fa-solid fa-gear mr-3 text-gray-400 w-5"></i> Settings
                        </div>
                        <div class="px-5 py-3 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center text-red-500 border-t mt-1 pt-3" wire:click="logout">
                             <i class="fa-solid fa-right-from-bracket mr-3 w-5"></i> Log out
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Container --}}
        <div style="padding: 10px 14px; background: white; flex-shrink: 0;">
            <div style="background: #f0f2f5; border-radius: 12px; display: flex; align-items: center; padding: 0 16px; height: 40px; border: 1px solid transparent; transition: all 0.2s;" class="focus-within:bg-white focus-within:shadow-sm focus-within:border-[#00a884]/40">
                <i class="fa-solid fa-magnifying-glass text-[#8696a0] text-sm"></i>
                <input type="text" wire:model="search" style="background: transparent; border: none; width: 100%; font-size: 15px; outline: none; margin-left: 12px; color: #111b21;" placeholder="Search or start new chat">
            </div>
        </div>

        {{-- Sidebar Content --}}
        <div style="flex: 1; overflow-y: auto;" class="custom-scrollbar">
            @if($showStories)
                {{-- Stories View --}}
                <div class="slide-in-left bg-[#f0f2f5] h-full flex flex-col">
                    <div style="background: #008069; color: white; padding: 20px; display: flex; align-items: flex-end; height: 108px; flex-shrink: 0;">
                        <i class="fa-solid fa-arrow-left cursor-pointer mr-6 text-xl hover:scale-110 transition-transform" wire:click="$set('showStories', false)"></i>
                        <span style="font-size: 19px; font-weight: 600;">Status</span>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar bg-white">
                        {{-- My Story --}}
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center gap-4 cursor-pointer hover:bg-gray-50 p-3 rounded-xl transition-all" wire:click="$set('viewingStory', 'create')">
                                <div class="relative">
                                    <img src="{{ auth()->user()->profile_photo_url }}" class="w-14 h-14 rounded-full border-2 border-gray-200">
                                    <div class="absolute bottom-0 right-0 w-6 h-6 bg-[#00a884] rounded-full flex items-center justify-center border-2 border-white">
                                        <i class="fa-solid fa-plus text-white text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-black text-[#111b21]">My Status</p>
                                    <p class="text-xs text-gray-400 font-medium">Tap to add status update</p>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Updates --}}
                        @if($this->stories->count() > 0)
                            <div class="p-6">
                                <p class="text-xs text-gray-400 font-black uppercase tracking-wider mb-4">Recent Updates</p>
                                @foreach($this->stories as $user)
                                    <div class="flex items-center gap-4 cursor-pointer hover:bg-gray-50 p-3 rounded-xl transition-all mb-2" 
                                         wire:click="viewStory({{ $user->activeStories->first()->id }})">
                                        <div class="relative">
                                            <div class="w-14 h-14 rounded-full p-0.5 bg-gradient-to-tr from-[#00a884] to-[#25d366]">
                                                <img src="{{ $user->profile_photo_url }}" class="w-full h-full rounded-full border-2 border-white">
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-[#111b21]">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-400 font-medium">{{ $user->activeStories->first()->created_at->diffForHumans() }}</p>
                                        </div>
                                        @if($user->id !== auth()->id())
                                            <span class="text-xs text-[#00a884] font-black">{{ $user->activeStories->count() }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($isCreatingGroup)
                {{-- New Group View --}}
                <div class="slide-in-left bg-[#f0f2f5] h-full flex flex-col">
                    <div style="background: #008069; color: white; padding: 20px; display: flex; align-items: flex-end; height: 108px; flex-shrink: 0;">
                        <i class="fa-solid fa-arrow-left cursor-pointer mr-6 text-xl hover:scale-110 transition-transform" wire:click="$set('isCreatingGroup', false)"></i>
                        <span style="font-size: 19px; font-weight: 600; letter-spacing: -0.5px;">New Group</span>
                    </div>
                    <div class="flex-1 overflow-y-auto">
                        <div class="p-10 bg-white flex flex-col items-center border-b">
                             <label class="cursor-pointer group relative">
                                <div class="w-40 h-40 rounded-full bg-[#f0f2f5] flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 group-hover:border-[#00a884] transition-all relative">
                                    @if($groupImage)
                                        <img src="{{ $groupImage->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center text-gray-400">
                                            <i class="fa-solid fa-camera text-4xl mb-2"></i>
                                            <p class="text-[10px] uppercase font-black">Add Icon</p>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" wire:model="groupImage" class="hidden">
                            </label>
                            <input type="text" wire:model.defer="groupName" style="width: 100%; border: none; border-bottom: 2px solid #008069; padding: 12px 0; outline: none; margin-top: 35px; font-size: 17px; text-align: center;" placeholder="Group Subject">
                        </div>
                        <div class="mt-3 bg-white p-2">
                            <h3 style="color: #008069; font-size: 13px; font-weight: 800; text-transform: uppercase; padding: 15px 20px; letter-spacing: 1.5px;">Contacts</h3>
                            <div class="divide-y divide-gray-50">
                                @foreach ($this->contacts as $contact)
                                    <div wire:click="toggleContactSelection({{ $contact->id }})" class="flex items-center p-4 hover:bg-[#f5f6f6] cursor-pointer transition-all">
                                        <div class="checkbox-container mr-5 {{ in_array($contact->id, $selectedContacts) ? 'checked' : '' }}"><i class="fa-solid fa-check"></i></div>
                                        <img src="{{ $contact->user->profile_photo_url }}" class="w-12 h-12 rounded-full border border-gray-100 shadow-sm">
                                        <div class="ml-4 flex-1">
                                            <div style="font-weight: 700; color: #111b21;">{{ $contact->name }}</div>
                                            <div style="font-size: 13px; color: #667781;">{{ $contact->user->email }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if(count($selectedContacts) > 0 && $groupName)
                        <div class="p-6 bg-white border-t flex justify-center sticky bottom-0 shadow-[0_-4px_10px_rgba(0,0,0,0.03)]">
                            <button wire:click="createGroup" class="bg-[#00a884] text-white w-16 h-16 rounded-full shadow-2xl hover:scale-105 active:scale-90 transition-all flex items-center justify-center">
                                <i class="fa-solid fa-check text-2xl"></i>
                            </button>
                        </div>
                    @endif
                </div>
            @elseif($showSettings)
                {{-- Settings View --}}
                <div class="slide-in-left bg-[#f0f2f5] h-full flex flex-col" x-data="{ activeTab: 'main' }">
                    
                    {{-- Main Settings --}}
                    <div x-show="activeTab === 'main'" class="h-full flex flex-col">
                        <div style="background: #008069; color: white; padding: 20px; display: flex; align-items: flex-end; height: 108px; flex-shrink: 0;">
                            <i class="fa-solid fa-arrow-left cursor-pointer mr-6 text-xl hover:scale-110 transition-transform" wire:click="toggleSettings"></i>
                            <span style="font-size: 19px; font-weight: 600;">Settings</span>
                        </div>
                        <div class="flex-1 overflow-y-auto p-4 space-y-4">
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-5 cursor-pointer hover:bg-gray-50 transition-all group relative">
                                <label class="relative cursor-pointer">
                                    <div class="relative">
                                        @if($profilePhoto)
                                            <img src="{{ $profilePhoto->temporaryUrl() }}" class="w-20 h-20 rounded-full border-4 border-white shadow-md object-cover">
                                        @else
                                            <img src="{{ auth()->user()->profile_photo_url }}" class="w-20 h-20 rounded-full border-4 border-white shadow-md object-cover">
                                        @endif
                                        <div class="absolute bottom-0 right-0 bg-[#00a884] p-1.5 rounded-full text-white text-[10px] border-2 border-white shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="fa-solid fa-camera"></i>
                                        </div>
                                    </div>
                                    <input type="file" wire:model="profilePhoto" class="hidden" accept="image/*">
                                </label>
                                <div class="flex-1">
                                    <h4 class="font-black text-lg text-[#111b21] group-hover:text-[#00a884] transition-colors">{{ auth()->user()->name }}</h4>
                                    <p class="text-sm text-gray-500 italic">"Hey there! I am using WhatsApp."</p>
                                    @if($profilePhoto)
                                        <button wire:click="saveProfilePhoto" class="mt-2 text-[11px] bg-[#00a884] text-white px-3 py-1 rounded-full font-bold shadow-sm hover:bg-[#008f72] transition-colors">
                                            Save Photo
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-50">
                                <div class="p-5 flex items-center gap-6 hover:bg-gray-50 cursor-pointer group" @click="activeTab = 'account'">
                                    <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-all">
                                        <i class="fa-solid fa-key text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-bold text-gray-700 block">Account</span>
                                        <span class="text-xs text-gray-400">Security, change number</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-gray-300"></i>
                                </div>
                                <div class="p-5 flex items-center gap-6 hover:bg-gray-50 cursor-pointer group" @click="activeTab = 'privacy'">
                                    <div class="w-10 h-10 bg-teal-50 text-teal-500 rounded-full flex items-center justify-center group-hover:bg-teal-500 group-hover:text-white transition-all">
                                        <i class="fa-solid fa-lock text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-bold text-gray-700 block">Privacy</span>
                                        <span class="text-xs text-gray-400">Last seen, profile photo</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-gray-300"></i>
                                </div>
                                <div class="p-5 flex items-center gap-6 hover:bg-gray-50 cursor-pointer group" @click="activeTab = 'chats'">
                                    <div class="w-10 h-10 bg-green-50 text-green-500 rounded-full flex items-center justify-center group-hover:bg-green-500 group-hover:text-white transition-all">
                                        <i class="fa-solid fa-message text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-bold text-gray-700 block">Chats</span>
                                        <span class="text-xs text-gray-400">Theme, wallpapers, history</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Account Settings --}}
                    <div x-show="activeTab === 'account'" class="h-full flex flex-col bg-white" x-cloak>
                        <div style="background: #008069; color: white; padding: 20px; display: flex; align-items: flex-end; height: 108px; flex-shrink: 0;">
                            <i class="fa-solid fa-arrow-left cursor-pointer mr-6 text-xl hover:scale-110 transition-transform" @click="activeTab = 'main'"></i>
                            <span style="font-size: 19px; font-weight: 600;">Account</span>
                        </div>
                        <div class="divide-y divide-gray-50">
                            {{-- Account Content --}}
                             <div class="p-5 hover:bg-gray-50 cursor-pointer flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <i class="fa-solid fa-shield-halved text-gray-400 w-6"></i>
                                    <span class="font-medium">Security notifications</span>
                                </div>
                            </div>
                            <div class="p-5 hover:bg-gray-50 cursor-pointer flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <i class="fa-solid fa-circle-check text-gray-400 w-6"></i>
                                    <span class="font-medium">Two-step verification</span>
                                </div>
                            </div>
                            <div class="p-5 hover:bg-gray-50 cursor-pointer flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <i class="fa-solid fa-mobile-screen-button text-gray-400 w-6"></i>
                                    <span class="font-medium">Change number</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Privacy Settings --}}
                    <div x-show="activeTab === 'privacy'" class="h-full flex flex-col bg-white" x-cloak>
                        <div style="background: #008069; color: white; padding: 20px; display: flex; align-items: flex-end; height: 108px; flex-shrink: 0;">
                            <i class="fa-solid fa-arrow-left cursor-pointer mr-6 text-xl hover:scale-110 transition-transform" @click="activeTab = 'main'"></i>
                            <span style="font-size: 19px; font-weight: 600;">Privacy</span>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <div class="p-5 hover:bg-gray-50 cursor-pointer">
                                <div class="font-medium text-[#111b21]">Last seen and online</div>
                                <div class="text-xs text-[#00a884] font-bold">Everyone</div>
                            </div>
                            <div class="p-5 hover:bg-gray-50 cursor-pointer">
                                <div class="font-medium text-[#111b21]">Profile photo</div>
                                <div class="text-xs text-[#00a884] font-bold">My contacts</div>
                            </div>
                            <div class="p-5 hover:bg-gray-50 cursor-pointer">
                                <div class="font-medium text-[#111b21]">Status</div>
                                <div class="text-xs text-[#00a884] font-bold">My contacts</div>
                            </div>
                            <div class="p-5 hover:bg-gray-50 cursor-pointer">
                                <div class="font-medium text-[#111b21]">Read receipts</div>
                                <div class="text-xs text-gray-400">If turned off, you won't send or receive read receipts.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Chat Settings --}}
                    <div x-show="activeTab === 'chats'" class="h-full flex flex-col bg-white" x-cloak>
                        <div style="background: #008069; color: white; padding: 20px; display: flex; align-items: flex-end; height: 108px; flex-shrink: 0;">
                            <i class="fa-solid fa-arrow-left cursor-pointer mr-6 text-xl hover:scale-110 transition-transform" @click="activeTab = 'main'"></i>
                            <span style="font-size: 19px; font-weight: 600;">Chats</span>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <div class="p-5 hover:bg-gray-50 cursor-pointer flex items-center justify-between" wire:click="toggleDarkMode">
                                <div>
                                    <div class="font-medium text-[#111b21]">Dark Mode</div>
                                    <div class="text-xs text-gray-400">{{ auth()->user()->dark_mode ? 'Enabled' : 'Disabled' }}</div>
                                </div>
                                <div class="w-12 h-6 {{ auth()->user()->dark_mode ? 'bg-[#00a884]' : 'bg-gray-200' }} rounded-full relative transition-colors">
                                    <div class="absolute {{ auth()->user()->dark_mode ? 'right-1' : 'left-1' }} top-1 w-4 h-4 bg-white rounded-full transition-all shadow-sm"></div>
                                </div>
                            </div>
                            <label class="p-5 hover:bg-gray-50 cursor-pointer block">
                                <div class="font-medium text-[#111b21]">Wallpaper</div>
                                <div class="text-xs text-[#00a884] font-bold">Default</div>
                                <input type="file" wire:model="chatWallpaper" class="hidden">
                            </label>
                            <div class="p-6">
                                <h4 class="text-[11px] font-black text-[#00a884] uppercase tracking-widest mb-4">Chat settings</h4>
                                <div class="flex items-center justify-between py-3">
                                    <span class="font-medium">Enter is send</span>
                                    <div class="w-10 h-5 bg-[#00a884] rounded-full relative"><div class="absolute right-1 top-1 w-3 h-3 bg-white rounded-full"></div></div>
                                </div>
                            </div>
                            <div class="p-5 hover:bg-gray-50 cursor-pointer flex items-center gap-5">
                                <i class="fa-solid fa-clock-rotate-left text-gray-400"></i>
                                <span class="font-medium">Chat history</span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($showStarred)
                {{-- Starred Messages View --}}
                <div class="slide-in-left bg-[#f0f2f5] h-full flex flex-col">
                    <div style="background: #008069; color: white; padding: 20px; display: flex; align-items: flex-end; height: 108px; flex-shrink: 0;">
                        <i class="fa-solid fa-arrow-left cursor-pointer mr-6 text-xl hover:scale-110 transition-transform" wire:click="toggleStarred"></i>
                        <span style="font-size: 19px; font-weight: 600;">Starred Messages</span>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar">
                        @forelse($this->starred_messages as $m)
                            <div class="p-4 px-6 bg-white mb-2 cursor-pointer hover:bg-gray-50 transition-all border-y border-black/[0.02]" 
                                 wire:click="open_chat({{ $m->chat_id }})">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $m->user->profile_photo_url }}" class="w-6 h-6 rounded-full">
                                        <span class="text-[12px] font-black text-[#00a884]">{{ $m->user->name }}</span>
                                        <span class="text-[10px] text-gray-400 font-bold px-2 py-0.5 bg-gray-100 rounded-md">in {{ $m->chat->name }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-bold">{{ $m->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-[14px] text-[#111b21] line-clamp-2">
                                    @if($m->type == 'image') <i class="fa-solid fa-camera mr-2 text-gray-400"></i> Photo
                                    @elseif($m->type == 'file') <i class="fa-solid fa-file mr-2 text-gray-400"></i> File
                                    @endif
                                    {{ $m->body }}
                                </div>
                            </div>
                        @empty
                            <div class="flex-1 flex flex-col items-center justify-center p-12 text-center select-none h-full">
                                <div class="w-28 h-28 bg-white rounded-full flex items-center justify-center mb-8 shadow-xl border border-yellow-100">
                                    <i class="fa-solid fa-star text-5xl text-yellow-400 animate-pulse"></i>
                                </div>
                                <h3 class="text-[#111b21] font-black text-xl mb-3">Keep it handy</h3>
                                <p class="text-gray-500 leading-relaxed font-light">Stars messages to easily find them later.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                {{-- Chats List --}}
                <div class="divide-y divide-gray-50 flex-1 overflow-y-auto custom-scrollbar">
                    @if(!$showArchived)
                    <div wire:click="$set('showArchived', true)" class="px-8 py-5 flex items-center gap-6 hover:bg-[#f5f6f6] cursor-pointer group transition-all border-b border-black/[0.03]">
                        <div class="text-[#00a884]">
                            <i class="fa-solid fa-box-archive text-lg"></i>
                        </div>
                        <span class="font-bold text-[#111b21] flex-1">Archived</span>
                        <span class="bg-[#f0f2f5] text-[#00a884] text-[11px] font-black px-2 py-0.5 rounded-md">{{ auth()->user()->chats()->where('chat_user.is_archived', true)->count() }}</span>
                    </div>
                    @else
                    <div wire:click="$set('showArchived', false)" class="px-8 py-5 flex items-center gap-6 hover:bg-[#f5f6f6] cursor-pointer group transition-all border-b border-black/[0.03] bg-[#f0f2f5]">
                        <i class="fa-solid fa-arrow-left text-[#00a884]"></i>
                        <span class="font-bold text-[#00a884] flex-1">Archived Chats</span>
                    </div>
                    @endif
                    @forelse ($this->chats as $item)
                        <div wire:click="open_chat({{$item->id}})" 
                             class="chat-item {{ $chat && $chat->id == $item->id ? 'active' : '' }}" 
                             style="display: flex; padding: 12px 16px; border-bottom: 1px solid #f7f7f7; cursor: pointer; height: 75px; align-items: center; transition: all 0.2s relative;">
                            
                            @if($chat && $chat->id == $item->id)
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#00a884]"></div>
                            @endif

                            <div class="relative shrink-0 flex-shrink-0">
                                <img src="{{ $item->image }}" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(0,0,0,0.05); shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                @if(!$item->is_group && $this->isUserOnline($item->otherUser?->id))
                                    <span class="absolute bottom-0 right-0 w-4 h-4 bg-[#1fa855] border-2 border-white rounded-full shadow-sm"></span>
                                @endif
                            </div>
                            <div style="flex: 1; margin-left: 15px; overflow: hidden; min-width: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2px;">
                                    <span style="font-weight: 700; font-size: 16.5px; color: #111b21; letter-spacing: -0.3px;" class="truncate pr-4">{{ $item->name }}</span>
                                    <span style="font-size: 12px; color: {{ $item->unread_messages ? '#1fa855' : '#667781' }}; font-weight: {{ $item->unread_messages ? '800' : '400' }}; flex-shrink: 0;">
                                        {{ $item->last_message_at ? ($item->last_message_at->isToday() ? $item->last_message_at->format('h:i A') : $item->last_message_at->format('d/m/y')) : '' }}
                                    </span>
                                </div>
                                    @php
                                        $userPivot = $item->users()->where('users.id', auth()->id())->first()->pivot;
                                        $isPinned = $userPivot->is_pinned;
                                        $isMuted = $userPivot->muted_until && now()->lt($userPivot->muted_until);
                                    @endphp
                                    <div style="font-size: 13.5px; color: #667781; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; min-width: 0; font-weight: 300;">
                                        @if($item->messages->last())
                                            @if($item->messages->last()->user_id == auth()->id())
                                                <i class="fa-solid fa-check-double {{ $item->messages->last()->is_read ? 'text-[#53bdeb]' : 'text-gray-400' }} mr-1.5" style="font-size: 11px;"></i>
                                            @endif
                                            @if($item->messages->last()->type == 'image') <i class="fa-solid fa-camera mr-1 text-[11px]"></i> Photo
                                            @elseif($item->messages->last()->type == 'file') <i class="fa-solid fa-file mr-1 text-[11px]"></i> File
                                            @endif
                                            {{ $item->messages->last()->body }}
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if($isMuted)
                                            <i class="fa-solid fa-volume-xmark text-gray-300 text-[13px]"></i>
                                        @endif
                                        @if($isPinned)
                                            <i class="fa-solid fa-thumbtack text-gray-300 text-[13px] rotate-45 transform"></i>
                                        @endif
                                        @if($item->unread_messages)
                                            <span class="bg-[#25d366] text-white text-[10px] font-black h-5 min-w-[20px] rounded-full flex items-center justify-center px-1.5 shadow-sm transform scale-110 ml-1">{{ $item->unread_messages }}</span>
                                        @endif
                                        
                                        {{-- Hover Action --}}
                                        <div class="relative group/action ml-1 transition-all" x-data="{ open: false }" @click.stop>
                                            <i class="fa-solid fa-chevron-down text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:text-gray-600" @click="open = !open"></i>
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                 class="absolute right-0 top-full mt-1 w-48 bg-white shadow-xl rounded-lg py-1 border border-black/5 z-[60] origin-top-right">
                                                <div class="px-4 py-2.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center gap-3" wire:click="togglePin({{ $item->id }}); open = false;">
                                                    <i class="fa-solid fa-thumbtack text-gray-400 w-4"></i> {{ $isPinned ? 'Unpin chat' : 'Pin chat' }}
                                                </div>
                                                <div class="px-4 py-2.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center gap-3" wire:click="toggleMute({{ $item->id }}); open = false;">
                                                    <i class="fa-solid fa-volume-xmark text-gray-400 w-4"></i> {{ $isMuted ? 'Unmute' : 'Mute notifications' }}
                                                </div>
                                                <div class="px-4 py-2.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center gap-3 text-red-500" wire:click="clearChat(); open = false;">
                                                    <i class="fa-solid fa-trash-can w-4"></i> Clear chat
                                                </div>
                                                <div class="px-4 py-2.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center gap-3" wire:click="toggleArchive({{ $item->id }}); open = false;">
                                                    <i class="fa-solid fa-box-archive text-gray-400 w-4"></i> {{ $item->pivot->is_archived ? 'Unarchive chat' : 'Archive chat' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center p-12 text-center opacity-40 select-none h-[400px]">
                            <div class="w-32 h-32 bg-[#f0f2f5] rounded-full flex items-center justify-center mb-6">
                                <i class="fa-solid fa-comments text-6xl text-gray-300"></i>
                            </div>
                            <h3 class="font-black text-gray-500 text-xl">Start a new chat</h3>
                            <p class="text-sm mt-2 max-w-[200px]">Connect with your contacts to begin messaging.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    {{-- Chat Area (Right) --}}
    <div class="chat-main" style="flex: 1; display: flex; flex-direction: column; background: #efeae2; position: relative; height: 100%; overflow: hidden;">
        @if ($chat)
            {{-- Chat Header --}}
            <div style="height: 64px; background: #f0f2f5; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid #d1d7db; flex-shrink: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; cursor: pointer; flex: 1; min-width: 0; padding: 6px 12px; margin-left: -12px; border-radius: 10px; transition: background 0.2s;" 
                     class="hover:bg-black/[0.03]"
                     wire:click="$toggle('showChatDetails')">
                    <div class="relative group">
                        <img src="{{ $chat->image }}" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid white; shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.2s;" class="group-hover:scale-105">
                        @if(!$chat->is_group && $this->isUserOnline($chat->otherUser?->id))
                            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-[#1fa855] border-2 border-[#f0f2f5] rounded-full shadow-sm"></span>
                        @endif
                    </div>
                    <div style="margin-left: 16px; min-width: 0;" class="truncate">
                        <div style="font-weight: 700; color: #111b21; font-size: 16.5px;" class="truncate tracking-tight">{{ $chat->name }}</div>
                        <div style="font-size: 12.5px; color: {{ $this->active ? '#008069' : '#667781' }}; font-weight: 600; margin-top: -1px;">
                            <template x-if="isOtherUserTyping">
                                <span class="text-[#00a884] animate-pulse">typing...</span>
                            </template>
                            <template x-if="!isOtherUserTyping">
                                <span>{{ $this->active ? 'online' : ($chat->otherUser?->last_seen_at ? 'last seen ' . $chat->otherUser->last_seen_at->diffForHumans() : 'offline') }}</span>
                            </template>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 32px; color: #54656f; font-size: 1.15rem; align-items: center;">
                    <i class="fa-solid fa-magnifying-glass cursor-pointer hover:text-[#00a884] transition-colors" 
                       title="Search in Chat" 
                       wire:click="$toggle('showChatSearch')"></i>
                    <div class="relative" x-data="{ open: false }">
                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer hover:text-[#00a884] p-2 transition-colors" @click="open = !open"></i>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             @click.away="open = false" x-cloak 
                             class="absolute right-0 top-full mt-2 w-56 bg-white shadow-2xl rounded-2xl py-2 border border-black/[0.03] z-50 origin-top-right">
                            <div class="px-6 py-3.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-bold flex items-center group/item" wire:click="$toggle('showChatDetails')" @click="open = false">
                                <i class="fa-solid fa-circle-info mr-4 text-gray-400 group-hover/item:text-[#00a884] transition-colors"></i> Contact info
                            </div>
                            <div class="px-6 py-3.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-bold flex items-center group/item">
                                <i class="fa-solid fa-star mr-4 text-gray-400 group-hover/item:text-yellow-400 transition-colors"></i> Starred messages
                            </div>
                            <div class="px-6 py-3.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-bold flex items-center group/item" wire:click="deleteForMe({{ $chat->messages->last()?->id }})" @click="open = false">
                                <i class="fa-solid fa-broom mr-4 text-gray-400 group-hover/item:text-red-500 transition-colors"></i> Clear messages
                            </div>
                            <div class="px-6 py-3.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-bold flex items-center text-red-600 border-t mt-1 pt-3">
                                <i class="fa-solid fa-trash-can mr-4"></i> Delete chat
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($showChatSearch)
                <div class="bg-white px-8 py-3.5 border-b border-black/[0.03] slide-in-top flex items-center gap-4 bg-[#f0f2f5]/50 backdrop-blur-sm sticky top-0 z-20">
                    <div class="flex-1 bg-white rounded-2xl px-5 py-2.5 flex items-center gap-4 shadow-sm border border-black/[0.02]">
                        <i class="fa-solid fa-magnifying-glass text-[#00a884] text-sm"></i>
                        <input type="text" wire:model.debounce.300ms="chatSearch" placeholder="Search for messages..." class="bg-transparent border-none outline-none text-sm w-full font-bold text-[#111b21] placeholder-gray-400">
                        @if($chatSearch)
                            <div class="w-5 h-5 bg-gray-100 rounded-full flex items-center justify-center cursor-pointer hover:bg-gray-200" wire:click="$set('chatSearch', '')">
                                <i class="fa-solid fa-xmark text-gray-500 text-[10px]"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Messages Area --}}
            <div style="flex: 1; overflow-y: auto; padding: 25px 8%; background-image: url('{{ auth()->user()->chat_wallpaper ? Storage::url(auth()->user()->chat_wallpaper) : asset('img/chat-bg.png') }}'); background-repeat: {{ auth()->user()->chat_wallpaper ? 'no-repeat' : 'repeat' }}; background-size: {{ auth()->user()->chat_wallpaper ? 'cover' : '400px' }}; background-position: center; position: relative;" 
                 class="custom-scrollbar" id="messages-body"
                 @scroll="checkScroll($event)">
                
                {{-- Scroll to bottom button --}}
                <div x-show="showScrollBtn" x-transition.opacity 
                     @click="scrollToBottom()"
                     class="absolute bottom-8 right-8 w-11 h-11 bg-white rounded-full shadow-2xl flex items-center justify-center cursor-pointer hover:bg-gray-50 z-40 border border-black/5 text-[#54656f] animate-bounce">
                    <i class="fa-solid fa-chevron-down pt-1"></i>
                </div>

                <div style="display: flex; flex-direction: column-reverse; min-height: 100%;">
                    <div id="final" style="height: 10px;"></div>
                    
                    @php 
                        $currentDate = null; 
                    @endphp

                    @foreach ($this->messages as $m)
                        @php
                            $msgDate = $m->created_at->format('Y-m-d');
                            $showDateHeader = ($currentDate !== $msgDate);
                            $currentDate = $msgDate;
                        @endphp
                        
                        <div style="display: flex; justify-content: {{ $m->user_id == auth()->id() ? 'flex-end' : 'flex-start' }}; margin-bottom: 3px; padding: 0 4px;" class="group" wire:key="msg-{{ $m->id }}">
                            <div class="bubble {{ $m->user_id == auth()->id() ? 'sent' : 'received' }}">
                                @if($chat->is_group && $m->user_id != auth()->id())
                                    <div style="font-size: 12.5px; font-weight: 800; color: #008069; margin-bottom: 4px; letter-spacing: -0.2px;">{{ $m->user->name }}</div>
                                @endif
                                
                                <div class="relative">
                                    {{-- Reply Content --}}
                                    @if($m->parent)
                                        <div class="mb-2 p-2 border-l-4 border-[#00a884] bg-black/[0.03] rounded-r-md cursor-pointer hover:bg-black/[0.05] transition-colors" onclick="document.getElementById('msg-{{ $m->parent_id }}')?.scrollIntoView({behavior: 'smooth', block: 'center'})">
                                            <div class="text-[11px] font-black text-[#00a884] uppercase tracking-tighter">{{ $m->parent->user_id == auth()->id() ? 'You' : $m->parent->user->name }}</div>
                                            <div class="text-[12.5px] text-gray-500 truncate max-w-[200px]">{{ $m->parent->body ?? ( $m->parent->type == 'image' ? 'Photo' : 'File' ) }}</div>
                                        </div>
                                    @endif

                                    @if($m->deleted_for_everyone)
                                        <div style="font-size: 14px; color: #8696A0; font-style: italic; display: flex; align-items: center; padding: 4px 10px 4px 0;">
                                            <i class="fa-solid fa-ban mr-3 text-[12px] opacity-60"></i> This message was deleted
                                        </div>
                                    @else
                                        @if ($m->type == 'image')
                                            <div class="mb-2 rounded-xl overflow-hidden shadow-sm border border-black/5 bg-gray-50/50 group/img relative">
                                                <img src="{{ Storage::url($m->file_path) }}" class="max-h-[550px] w-full object-cover cursor-zoom-in transition-all group-hover/img:brightness-95" onclick="window.open(this.src)">
                                                <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/5 transition-all"></div>
                                            </div>
                                        @elseif($m->type == 'file')
                                            <div class="bg-black/[0.03] p-4 rounded-xl flex items-center gap-5 border border-black/[0.05] hover:bg-black/[0.06] transition-all cursor-pointer group/file mb-2">
                                                <div class="bg-[#34b7f1] p-3 rounded-xl shadow-lg shadow-blue-500/20 text-white transform group-hover/file:scale-110 transition-transform">
                                                    <i class="fa-solid fa-file-invoice text-2xl"></i>
                                                </div>
                                                <div class="flex-1 overflow-hidden">
                                                    <div style="font-size: 14px; font-weight: 800; color: #111b21;" class="truncate mb-0.5">{{ basename($m->file_path) }}</div>
                                                    <div style="font-size: 10.5px; color: #008069; font-weight: 900; text-transform: uppercase; letter-spacing: 0.8px;">Download File</div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($m->body)
                                            <div style="font-size: 14.8px; color: #111b21; line-height: 1.5; white-space: pre-wrap; word-break: break-all; padding-right: 20px; font-weight: 300;">{{ $m->body }}</div>
                                        @endif
                                    @endif

                                    <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 4px; gap: 4px; opacity: 0.8; height: 14px;">
                                        @if($m->starredByUsers()->where('user_id', auth()->id())->exists())
                                            <i class="fa-solid fa-star text-yellow-400 text-[9px] mr-1"></i>
                                        @endif
                                        <span style="font-size: 10px; color: #667781; font-weight: 700; text-transform: uppercase;">{{ $m->created_at->format('h:i A') }}</span>
                                        @if($m->user_id == auth()->id() && !$m->deleted_for_everyone)
                                            <i class="fa-solid fa-check-double" style="font-size: 12px; color: {{ $m->is_read ? '#53bdeb' : '#8696a0' }}; transition: color 0.3s;"></i>
                                        @endif
                                    </div>

                                    {{-- Reactions Display --}}
                                    @php $reactions = $m->reactions()->get()->groupBy('emoji'); @endphp
                                    @if($reactions->count() > 0)
                                        <div class="absolute -bottom-4 {{ $m->user_id == auth()->id() ? 'left-0' : 'right-0' }} flex gap-1 z-10">
                                            @foreach($reactions as $emoji => $group)
                                                <div class="bg-white rounded-full px-1.5 py-0.5 shadow-sm border border-black/5 text-[11px] flex items-center gap-1 cursor-pointer hover:bg-gray-50 transition-colors"
                                                     wire:click="toggleReaction({{ $m->id }}, '{{ $emoji }}')">
                                                    <span>{{ $emoji }}</span>
                                                    @if($group->count() > 1)
                                                        <span class="text-[9px] font-black text-gray-500">{{ $group->count() }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    {{-- Hover Actions --}}
                                    <div class="absolute top-[-35px] {{ $m->user_id == auth()->id() ? 'right-0' : 'left-0' }} opacity-0 group-hover:opacity-100 transition-all flex items-center gap-1.5 z-20 translate-y-2 group-hover:translate-y-0">
                                         <div class="bg-white/95 backdrop-blur px-3 py-1.5 rounded-full shadow-2xl border border-black/5 flex gap-2.5 animate-scale-in">
                                             <span class="cursor-pointer hover:scale-150 transition-transform active:scale-90 text-[16px]" title="Like" wire:click="toggleReaction({{ $m->id }}, '👍')">👍</span>
                                             <span class="cursor-pointer hover:scale-150 transition-transform active:scale-90 text-[16px]" title="Love" wire:click="toggleReaction({{ $m->id }}, '❤️')">❤️</span>
                                             <span class="cursor-pointer hover:scale-150 transition-transform active:scale-90 text-[16px]" title="Laugh" wire:click="toggleReaction({{ $m->id }}, '😂')">😂</span>
                                             <span class="cursor-pointer hover:scale-150 transition-transform active:scale-90 text-[16px]" title="Wow" wire:click="toggleReaction({{ $m->id }}, '😮')">😮</span>
                                             <span class="cursor-pointer hover:scale-150 transition-transform active:scale-90 text-[16px]" title="Sad" wire:click="toggleReaction({{ $m->id }}, '😢')">😢</span>
                                         </div>
                                     </div>

                                     <div class="absolute top-[-5px] {{ $m->user_id == auth()->id() ? 'right-[-45px]' : 'left-[-45px]' }} opacity-0 group-hover:opacity-100 transition-all flex flex-col gap-1.5 z-20">
                                         <div class="bg-white/90 backdrop-blur p-1.5 rounded-full shadow-lg border border-gray-100 flex flex-col gap-1 text-center">
                                             <button class="p-1 px-2 text-gray-400 hover:text-[#00a884] transition-colors" title="Reply" wire:click="setReply({{ $m->id }})">
                                                 <i class="fa-solid fa-reply text-[11px]"></i>
                                             </button>
                                             <button class="p-1 px-2 {{ $m->starredByUsers()->where('user_id', auth()->id())->exists() ? 'text-yellow-400' : 'text-gray-400 hover:text-yellow-400' }} transition-colors" wire:click="toggleStar({{ $m->id }})" title="Star">
                                                 <i class="fa-solid fa-star text-[11px]"></i>
                                             </button>
                                             <button class="p-1 px-2 text-gray-400 hover:text-red-500 transition-colors" wire:click="deleteForMe({{ $m->id }})" title="Delete">
                                                 <i class="fa-solid fa-trash-can text-[11px]"></i>
                                             </button>
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>

                        @if($showDateHeader)
                            <div class="flex justify-center my-8 sticky top-4 z-20 pointer-events-none">
                                <span class="bg-white/80 backdrop-blur px-5 py-1.5 rounded-xl shadow-sm border border-gray-100 text-[11.5px] font-black text-[#54656f] uppercase tracking-widest pointer-events-auto">
                                    @if($m->created_at->isToday()) Today
                                    @elseif($m->created_at->isYesterday()) Yesterday
                                    @else {{ $m->created_at->format('F d, Y') }}
                                    @endif
                                </span>
                            </div>
                        @endif
                    @endforeach

                    @if($this->messages->count() >= $page * 20)
                        <div class="flex justify-center py-10">
                            <button wire:click="loadMore" class="bg-white/95 backdrop-blur px-8 py-2.5 rounded-full shadow-xl border border-gray-50 text-[12px] font-black text-[#008069] uppercase tracking-[2px] hover:bg-white hover:scale-105 transition-all transform active:scale-95 group">
                                <i class="fa-solid fa-clock-rotate-left mr-2 group-hover:rotate-[-45deg] transition-transform"></i> Load History
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer / Input Bar --}}
            <div style="background: #f0f2f5; padding: 12px 24px; display: flex; align-items: center; gap: 20px; border-top: 1px solid #d1d7db; flex-shrink: 0; z-index: 10; position: relative;" 
                 x-data="{ isRecording: false, recordTime: '00:00', timer: null, startRecording() { this.isRecording = true; let s = 0; this.timer = setInterval(() => { s++; let m = Math.floor(s/60); let ss = s%60; this.recordTime = `${m.toString().padStart(2, '0')}:${ss.toString().padStart(2, '0')}`; }, 1000); }, stopRecording() { this.isRecording = false; clearInterval(this.timer); this.recordTime = '00:00'; } }">
                
                <div style="display: flex; align-items: center; gap: 22px; color: #54656f;">
                    <div class="relative">
                        <i class="fa-regular fa-face-smile text-[28px] cursor-pointer hover:text-[#00a884] transition-colors active:scale-90" @click="$set('showEmojiPicker', !showEmojiPicker)"></i>
                    </div>
                    <i class="fa-solid fa-plus text-[24px] cursor-pointer hover:text-[#00a884] transition-all hover:rotate-90 active:scale-90" @click="$refs.fileInput.click()"></i>
                    <input type="file" x-ref="fileInput" wire:model="media" class="hidden">
                </div>
                
                <form wire:submit.prevent="sendMessage()" style="flex: 1; display: flex; align-items: center; gap: 20px; position: relative;">
                    <div class="flex-1 relative">
                        {{-- Reply Preview Bar --}}
                        @if($replyingTo)
                            <div class="absolute bottom-[52px] left-0 right-0 bg-white shadow-xl rounded-t-2xl p-4 border-l-[6px] border-[#00a884] animate-soft-up z-0 flex items-center justify-between group">
                                <div class="flex-1 overflow-hidden">
                                    <div class="text-[11px] font-black text-[#00a884] uppercase">{{ $replyingTo->user_id == auth()->id() ? 'You' : $replyingTo->user->name }}</div>
                                    <div class="text-[13px] text-gray-500 truncate">{{ $replyingTo->body ?? ($replyingTo->type == 'image' ? 'Photo' : 'File') }}</div>
                                </div>
                                <i class="fa-solid fa-xmark cursor-pointer text-gray-300 hover:text-red-500 p-2" wire:click="cancelReply"></i>
                            </div>
                        @endif

                        <div x-show="!isRecording" class="w-full">
                            <input type="text" 
                                   wire:model="bodyMessage" 
                                   class="w-full bg-white border-none rounded-xl px-5 py-3 outline-none font-medium text-[15.5px] shadow-sm focus:shadow-md transition-all h-[46px]" 
                                   placeholder="Type a message">
                        </div>
                        
                        <div x-show="isRecording" x-cloak class="flex-1 bg-white rounded-xl h-[46px] flex items-center px-6 justify-between animate-soft-up shadow-inner border border-red-50">
                            <div class="flex items-center gap-4">
                                <div class="w-2.5 h-2.5 bg-red-600 rounded-full animate-ping"></div>
                                <span class="text-sm font-black text-gray-800 tracking-tighter" x-text="recordTime"></span>
                                <span class="text-xs text-gray-400 italic">Recording audio...</span>
                            </div>
                            <div class="flex gap-4">
                                <span class="text-[11px] text-[#ea0038] font-black uppercase tracking-widest cursor-pointer hover:underline" @click="stopRecording()">Cancel</span>
                            </div>
                        </div>
                    </div>

                    @if($bodyMessage || $media)
                        <button type="submit" 
                                style="background: #00a884; color: white; width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; shadow: 0 4px 12px rgba(0,168,132,0.4); transition: all 0.3s;" 
                                class="hover:scale-110 active:scale-90 transform group">
                            <i class="fa-solid fa-paper-plane text-xl ml-0.5 group-hover:translate-x-0.5 group-hover:translate-y-[-0.5px]"></i>
                        </button>
                    @else
                        <button type="button" 
                                x-show="!isRecording"
                                @click="startRecording()" 
                                style="color: #54656f; font-size: 26px; transition: all 0.2s;" 
                                class="hover:text-[#00a884] hover:scale-110 active:scale-90">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                        <button type="button" 
                                x-show="isRecording"
                                x-cloak
                                @click="stopRecording()"
                                style="background: #00a884; color: white; width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; shadow: 0 4px 12px rgba(0,168,132,0.4);" 
                                class="hover:scale-110 active:scale-90">
                            <i class="fa-solid fa-check text-xl"></i>
                        </button>
                    @endif
                </form>

                {{-- Image Preview Pop-up --}}
                @if($media)
                    <div class="absolute bottom-[85px] left-6 right-6 bg-white/98 backdrop-blur p-8 rounded-[32px] shadow-2xl border border-gray-100 z-50 animate-scale-in flex gap-8 items-center border-l-[6px] border-l-[#00a884]">
                        <div class="w-28 h-28 rounded-2xl overflow-hidden shadow-2xl border-2 border-white bg-gray-50 flex items-center justify-center flex-shrink-0 group/prev">
                            @if (in_array(strtolower($media->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                <img src="{{ $media->temporaryUrl() }}" class="w-full h-full object-cover group-hover/prev:scale-110 transition-transform">
                            @else
                                <i class="fa-solid fa-file-invoice text-4xl text-gray-300"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[11px] font-black text-[#00a884] uppercase tracking-[0.2em] bg-teal-50 px-3 py-1 rounded-full">Media Ready</span>
                                <i class="fa-solid fa-circle-xmark cursor-pointer text-gray-300 hover:text-red-500 text-2xl transition-colors" wire:click="$set('media', null)"></i>
                            </div>
                            <h4 class="text-base font-black text-[#111b21] truncate">{{ $media->getClientOriginalName() }}</h4>
                            <p class="text-[13px] text-gray-400 font-medium italic mt-1">Ready to send media message...</p>
                        </div>
                        <button wire:click="sendMessage" class="bg-[#00a884] text-white px-10 py-4 rounded-2xl font-black shadow-2xl shadow-teal-500/30 hover:bg-[#008069] transition-all transform active:scale-95 uppercase tracking-widest text-xs">Send Image</button>
                    </div>
                @endif
            </div>

            {{-- Contact Info Slide-out --}}
            @if($showChatDetails)
                <div style="position: absolute; right: 0; top: 0; width: 420px; height: 100%; background: white; border-left: 1px solid #d1d7db; z-index: 50; display: flex; flex-direction: column;" class="slide-in-right shadow-2xl">
                    <div style="height: 64px; background: #f0f2f5; display: flex; align-items: center; padding: 0 28px; border-bottom: 1px solid #d1d7db; flex-shrink: 0;">
                        <i class="fa-solid fa-xmark cursor-pointer mr-10 text-xl text-gray-400 hover:text-red-500 hover:rotate-90 transition-all" wire:click="$set('showChatDetails', false)"></i>
                        <span style="font-weight: 800; font-size: 17px; color: #111b21; tracking-tight">Chat Details</span>
                    </div>
                    <div style="flex: 1; overflow-y: auto; background: #f0f2f5;" class="custom-scrollbar">
                        {{-- Profile Info --}}
                        <div style="background: white; padding: 45px 25px; text-align: center; border-bottom: 1px solid #e9edef; margin-bottom: 12px; shadow-sm">
                            <div class="relative inline-block mb-10 group">
                                <div class="absolute inset-0 bg-[#00a884]/20 blur-3xl rounded-full scale-75 group-hover:scale-100 transition-transform"></div>
                                <img src="{{ $chat->image }}" style="width: 220px; height: 220px; border-radius: 50%; object-fit: cover; border: 6px solid white; box-shadow: 0 10px 30px rgba(0,0,0,0.15); transition: transform 0.4s;" class="relative z-10 group-hover:scale-105">
                            </div>
                            <h2 style="font-size: 24px; font-weight: 900; color: #111b21; letter-spacing: -1px; margin-bottom: 6px;">{{ $chat->name }}</h2>
                            <p style="color: #667781; font-size: 16px; font-weight: 500; opacity: 0.8;">{{ $chat->otherUser?->email ?? 'Interactive Group' }}</p>
                        </div>

                        {{-- Section: Actions --}}
                        <div style="background: white; padding: 15px 25px; border-bottom: 1px solid #e9edef; margin-bottom: 12px;">
                            @php
                                $chatPivot = $chat->users()->where('users.id', auth()->id())->first()->pivot;
                            @endphp
                            <div class="flex items-center justify-between p-4 hover:bg-gray-50 rounded-xl cursor-pointer transition-all" wire:click="toggleMute({{ $chat->id }})">
                                <div class="flex items-center gap-4">
                                    <i class="fa-solid fa-bell-slash text-gray-400 w-5"></i>
                                    <span class="font-bold text-[#111b21]">Mute Notifications</span>
                                </div>
                                <div class="w-10 h-5 {{ $chatPivot->muted_until && now()->lt($chatPivot->muted_until) ? 'bg-[#00a884]' : 'bg-gray-200' }} rounded-full relative transition-colors">
                                    <div class="absolute {{ $chatPivot->muted_until && now()->lt($chatPivot->muted_until) ? 'right-1' : 'left-1' }} top-1 w-3 h-3 bg-white rounded-full transition-all"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-4 hover:bg-gray-50 rounded-xl cursor-pointer transition-all" wire:click="togglePin({{ $chat->id }})">
                                <div class="flex items-center gap-4">
                                    <i class="fa-solid fa-thumbtack text-gray-400 w-5"></i>
                                    <span class="font-bold text-[#111b21]">Pin Chat</span>
                                </div>
                                <div class="w-10 h-5 {{ $chatPivot->is_pinned ? 'bg-[#00a884]' : 'bg-gray-200' }} rounded-full relative transition-colors">
                                    <div class="absolute {{ $chatPivot->is_pinned ? 'right-1' : 'left-1' }} top-1 w-3 h-3 bg-white rounded-full transition-all"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-4 hover:bg-gray-50 rounded-xl cursor-pointer transition-all" wire:click="toggleArchive({{ $chat->id }})">
                                <div class="flex items-center gap-4">
                                    <i class="fa-solid fa-box-archive text-gray-400 w-5"></i>
                                    <span class="font-bold text-[#111b21]">Archive Chat</span>
                                </div>
                                <div class="w-10 h-5 {{ $chatPivot->is_archived ? 'bg-[#00a884]' : 'bg-gray-200' }} rounded-full relative transition-colors">
                                    <div class="absolute {{ $chatPivot->is_archived ? 'right-1' : 'left-1' }} top-1 w-3 h-3 bg-white rounded-full transition-all"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Chat Statistics --}}
                        @if($this->chat_stats)
                            <div style="background: white; padding: 25px; border-bottom: 1px solid #e9edef; margin-bottom: 12px;">
                                <div style="color: #667781; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px;">Chat Statistics</div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl text-center">
                                        <div class="text-3xl font-black text-blue-600 mb-1">{{ number_format($this->chat_stats['total_messages']) }}</div>
                                        <div class="text-xs text-blue-700 font-bold uppercase tracking-wider">Total Messages</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl text-center">
                                        <div class="text-3xl font-black text-green-600 mb-1">{{ number_format($this->chat_stats['my_messages']) }}</div>
                                        <div class="text-xs text-green-700 font-bold uppercase tracking-wider">Your Messages</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl text-center">
                                        <div class="text-3xl font-black text-purple-600 mb-1">{{ number_format($this->chat_stats['media_count']) }}</div>
                                        <div class="text-xs text-purple-700 font-bold uppercase tracking-wider">Media Files</div>
                                    </div>
                                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-xl text-center">
                                        <div class="text-3xl font-black text-orange-600 mb-1">{{ $this->chat_stats['first_message'] ? $this->chat_stats['first_message']->diffInDays(now()) : 0 }}</div>
                                        <div class="text-xs text-orange-700 font-bold uppercase tracking-wider">Days Old</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Section: Media --}}
                        <div style="background: white; padding: 25px; border-bottom: 1px solid #e9edef; margin-bottom: 12px;">
                            <div class="flex justify-between items-center mb-6">
                                <div style="color: #667781; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px;">Media, Links & Docs</div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-sm"></i>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                @forelse($chat->messages()->where('type', 'image')->latest()->limit(3)->get() as $mediaMsg)
                                    <div class="aspect-square rounded-xl overflow-hidden shadow-sm border border-gray-100 cursor-pointer hover:brightness-75 transition-all">
                                        <img src="{{ Storage::url($mediaMsg->file_path) }}" class="w-full h-full object-cover">
                                    </div>
                                @empty
                                    <div class="col-span-3 py-6 text-center text-gray-300 italic text-sm">No recent media.</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Group Participants --}}
                        @if($chat->is_group)
                            <div style="background: white; padding: 0; border-bottom: 1px solid #e9edef; margin-bottom: 12px;">
                                @php
                                    $currentUserPivot = $chat->users()->where('users.id', auth()->id())->first()->pivot;
                                    $isCurrentUserAdmin = $currentUserPivot->is_admin;
                                @endphp
                                
                                <div style="padding: 24px 28px; font-weight: 900; color: #008069; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; display: flex; justify-content: space-between; align-items: center;">
                                    <span>{{ $chat->users->count() }} Members</span>
                                    <div class="flex items-center gap-3">
                                        @if($isCurrentUserAdmin)
                                            <button wire:click="$set('showAddMember', true)" class="bg-[#00a884] text-white px-3 py-1.5 rounded-lg text-[11px] font-black hover:bg-[#008069] transition-all shadow-sm">
                                                <i class="fa-solid fa-user-plus mr-1"></i> Add Member
                                            </button>
                                        @endif
                                        <i class="fa-solid fa-user-group text-teal-100"></i>
                                    </div>
                                </div>
                                <div class="divide-y divide-gray-50 bg-[#fafafa]/50">
                                    @foreach($chat->users as $u)
                                        <div class="flex items-center p-4 px-8 hover:bg-white group/member transition-all">
                                            <div class="relative">
                                                <img src="{{ $u->profile_photo_url }}" class="w-12 h-12 rounded-full border-2 border-white shadow-md">
                                                @if($this->isUserOnline($u->id))
                                                    <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                                                @endif
                                            </div>
                                            <div class="ml-5 flex-1 overflow-hidden">
                                                <div class="flex items-center gap-2">
                                                    <p style="font-weight: 800; font-size: 15.5px; color: #111b21;" class="group-hover/member:text-[#00a884] transition-colors truncate">{{ $u->name }}</p>
                                                    @if($u->pivot->is_admin)
                                                        <span class="bg-[#00a884] text-white text-[9px] px-2 py-0.5 rounded-md font-black uppercase tracking-wider">Admin</span>
                                                    @endif
                                                </div>
                                                <p style="font-size: 12.5px; color: #9ca3af; font-weight: 600;">{{ $u->email }}</p>
                                            </div>
                                            @if($u->id == auth()->id())
                                                <span style="font-size: 10px; background: #00a884; color: white; padding: 3px 10px; border-radius: 10px; font-weight: 900; letter-spacing: 0.5px;">YOU</span>
                                            @elseif($isCurrentUserAdmin)
                                                <div class="relative" x-data="{ open: false }">
                                                    <i class="fa-solid fa-ellipsis-vertical text-gray-300 hover:text-gray-600 cursor-pointer p-2 opacity-0 group-hover/member:opacity-100 transition-all" @click="open = !open"></i>
                                                    <div x-show="open" @click.away="open = false" x-cloak
                                                         class="absolute right-0 top-full mt-1 w-48 bg-white shadow-xl rounded-lg py-1 border border-black/5 z-50">
                                                        <div class="px-4 py-2.5 hover:bg-[#f5f6f6] cursor-pointer text-sm font-medium flex items-center gap-3" 
                                                             wire:click="toggleAdmin({{ $u->id }}); open = false;">
                                                            <i class="fa-solid fa-crown text-gray-400 w-4"></i> 
                                                            {{ $u->pivot->is_admin ? 'Dismiss as admin' : 'Make admin' }}
                                                        </div>
                                                        <div class="px-4 py-2.5 hover:bg-red-50 cursor-pointer text-sm font-medium flex items-center gap-3 text-red-500" 
                                                             wire:click="removeMember({{ $u->id }}); open = false;">
                                                            <i class="fa-solid fa-user-minus w-4"></i> Remove member
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Add Member Modal --}}
                        @if($showAddMember && $chat->is_group)
                            <div class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center" wire:click="$set('showAddMember', false)">
                                <div class="bg-white rounded-2xl shadow-2xl w-[500px] max-h-[600px] flex flex-col" @click.stop>
                                    <div class="p-6 border-b border-gray-100">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-xl font-black text-[#111b21]">Add Member</h3>
                                            <i class="fa-solid fa-xmark text-gray-400 hover:text-red-500 cursor-pointer text-xl transition-colors" wire:click="$set('showAddMember', false)"></i>
                                        </div>
                                        <div class="bg-[#f0f2f5] rounded-xl px-4 py-2.5 flex items-center gap-3">
                                            <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                                            <input type="text" wire:model.debounce.300ms="memberSearch" placeholder="Search contacts..." class="bg-transparent border-none outline-none text-sm w-full font-medium">
                                        </div>
                                    </div>
                                    <div class="flex-1 overflow-y-auto custom-scrollbar">
                                        @forelse($this->available_contacts as $contact)
                                            <div class="p-4 px-6 hover:bg-gray-50 cursor-pointer flex items-center gap-4 border-b border-gray-50 transition-all group/contact"
                                                 wire:click="addMember({{ $contact->contact_id }})">
                                                <img src="{{ $contact->user->profile_photo_url }}" class="w-12 h-12 rounded-full border-2 border-white shadow-md">
                                                <div class="flex-1">
                                                    <p class="font-bold text-[#111b21] group-hover/contact:text-[#00a884] transition-colors">{{ $contact->name }}</p>
                                                    <p class="text-xs text-gray-400 font-medium">{{ $contact->user->email }}</p>
                                                </div>
                                                <i class="fa-solid fa-user-plus text-gray-300 group-hover/contact:text-[#00a884] transition-colors"></i>
                                            </div>
                                        @empty
                                            <div class="p-12 text-center">
                                                <i class="fa-solid fa-users-slash text-5xl text-gray-200 mb-4"></i>
                                                <p class="text-gray-400 font-medium">No contacts available to add</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- DANGER ZONE --}}
                        <div style="background: white; padding: 15px; border-bottom: 1px solid #e9edef;">
                            @if(!$chat->is_group)
                            <div class="flex items-center text-[#ea0038] p-5 hover:bg-red-50 rounded-2xl cursor-pointer transition-all gap-8 group/del" wire:click="blockUser()">
                                <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center group-hover/del:bg-red-500 group-hover/del:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-ban text-2xl"></i>
                                </div>
                                <span style="font-weight: 900; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;">Block {{ $chat->name }}</span>
                            </div>
                            @endif
                            <div class="flex items-center text-[#ea0038] p-5 hover:bg-red-50 rounded-2xl cursor-pointer transition-all gap-8 group/del" wire:click="clearChat()">
                                <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center group-hover/del:bg-red-500 group-hover/del:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-trash-can text-2xl"></i>
                                </div>
                                <span style="font-weight: 900; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;">Clear Chat Content</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @else
            {{-- Landing Experience --}}
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: #f0f2f5; position: relative; overflow: hidden;">
                <div class="absolute inset-0 z-0 opacity-[0.03]" style="background-image: url('{{ asset('img/chat-bg.png') }}'); background-repeat: repeat; background-size: 400px;"></div>
                <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-[#00a884] rounded-full blur-[150px] opacity-[0.06]"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-500 rounded-full blur-[150px] opacity-[0.06]"></div>
                
                <div class="relative z-10 animate-soft-up">
                    <div class="relative inline-block mb-10">
                        <img src="{{ asset('img/intro-whatsapp.png') }}" style="width: 420px; opacity: 0.95; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.15));" class="animate-pulse-slow">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#f0f2f5] to-transparent h-1/4 bottom-0"></div>
                    </div>
                    <h1 style="font-size: 44px; font-weight: 900; color: #41525d; margin-bottom: 20px; letter-spacing: -2px;">WhatsApp Web Pro</h1>
                    <p style="color: #667781; max-width: 520px; line-height: 1.8; font-size: 16px; font-weight: 300; padding: 0 40px; margin: 0 auto 50px;">
                        Connect seamlessly with friends and family. Send messages, share media, and experience a modern, privacy-focused chat environment.
                    </p>
                    <div style="display: flex; align-items: center; justify-content: center; gap: 12px; color: #aebac1; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 3px; border-top: 1px solid #d1d7db; padding-top: 35px; width: 350px; margin: 0 auto;">
                        <i class="fa-solid fa-shield-halved" style="font-size: 14px; color: #1fa855;"></i> End-to-end encrypted
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 12px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        
        .chat-item.active { background: #f0f2f5; position: relative; }
        .chat-item:hover:not(.active) { background: #f9f9f9; }
        
        .bubble { 
            position: relative; 
            padding: 8px 14px 4px; 
            border-radius: 12px; 
            box-shadow: 0 1.5px 2px rgba(0,0,0,0.1); 
            max-width: 82%;
            word-wrap: break-word;
            animation: bubbleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes bubbleIn { from { transform: translateY(5px) scale(0.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

        .bubble.sent { 
            background: #d9fdd3; 
            margin-left: auto; 
        }
        .bubble.received { 
            background: white; 
            margin-right: auto; 
        }

        /* Dark Mode Styles */
        .dark-mode .sidebar,
        .dark-mode .chat-item,
        .dark-mode .bubble.received {
            background: #111b21 !important;
            color: #e9edef !important;
            border-color: #2a3942 !important;
        }

        .dark-mode .chat-item.active {
            background: #2a3942 !important;
        }

        .dark-mode .chat-item:hover:not(.active) {
            background: #1f2c34 !important;
        }

        .dark-mode .bubble.sent {
            background: #005c4b !important;
            color: #e9edef !important;
        }

        .dark-mode input,
        .dark-mode textarea {
            background: #2a3942 !important;
            color: #e9edef !important;
            border-color: #2a3942 !important;
        }

        .dark-mode input::placeholder,
        .dark-mode textarea::placeholder {
            color: #8696a0 !important;
        }

        .dark-mode [style*="background: #f0f2f5"],
        .dark-mode [style*="background:#f0f2f5"] {
            background: #111b21 !important;
        }

        .dark-mode [style*="background: white"],
        .dark-mode [style*="background:white"] {
            background: #1f2c34 !important;
            color: #e9edef !important;
        }

        .dark-mode [style*="color: #111b21"],
        .dark-mode [style*="color:#111b21"] {
            color: #e9edef !important;
        }

        .dark-mode [style*="color: #667781"],
        .dark-mode [style*="color:#667781"] {
            color: #8696a0 !important;
        }

        .dark-mode .border-gray-100,
        .dark-mode .border-gray-50 {
            border-color: #2a3942 !important;
        }

        .dark-mode .bg-gray-50,
        .dark-mode .bg-gray-100 {
            background: #2a3942 !important;
        }

        .dark-mode .text-gray-400,
        .dark-mode .text-gray-500 {
            color: #8696a0 !important;
        }
        
        /* Settings Modal Dark Mode Fixes */
        .dark-mode .bg-white {
            background-color: #111b21 !important;
            color: #e9edef !important;
            border-color: #2a3942 !important;
        }
        
        .dark-mode .divide-gray-50 > :not([hidden]) ~ :not([hidden]) {
            border-color: #2a3942 !important;
        }

        .dark-mode .hover\:bg-gray-50:hover {
            background-color: #202c33 !important;
        }

        /* Font Optimization */
        body, .font-family-outfit {
            font-family: 'Outfit', sans-serif !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .dark-mode .text-\[\#111b21\] {
            color: #e9edef !important;
        }

        .dark-mode .shadow-2xl,
        .dark-mode .shadow-xl {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.3) !important;
        }

        .dark-mode .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
        }

        /* Typing Indicator Animation */
        .typing-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00a884;
            animation: typingDot 1.4s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typingDot {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.7; }
            30% { transform: translateY(-10px); opacity: 1; }
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        .bubble.sent { background: #dcf8c6; border-top-right-radius: 0; }
        .bubble.sent::after { 
            content: ''; position: absolute; top: 0; right: -10px; border-left: 12px solid #dcf8c6; border-bottom: 12px solid transparent; 
        }
        .bubble.received { background: white; border-top-left-radius: 0; }
        .bubble.received::after { 
            content: ''; position: absolute; top: 0; left: -10px; border-right: 12px solid white; border-bottom: 12px solid transparent; 
        }

        .checkbox-container { border: 2.5px solid #ced3d6; width: 24px; height: 24px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: transparent; background: white; transition: all 0.3s ease; }
        .checkbox-container.checked { background: #00a884; border-color: #00a884; color: white; transform: scale(1.1) rotate(5deg); }

        .slide-in-left { animation: slideInLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideInLeft { from { transform: translateX(-40px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        
        .slide-in-right { animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }

        .animate-scale-in { animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes scaleIn { from { transform: scale(0.8) translateY(20px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }

        .animate-soft-up { animation: softUp 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes softUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        @keyframes pulseSlow { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
        .animate-pulse-slow { animation: pulseSlow 5s infinite ease-in-out; }

        [x-cloak] { display: none !important; }
    </style>

    @push('js')
        <script>
            function chatHandler(){
                return {
                    chat_id: @entangle('chat_id'),
                    isOtherUserTyping: false,
                    showScrollBtn: false,
                    typingTimeout: null,
                    init() {
                        Echo.private('App.Models.User.' + {{ auth()->id() }})
                            .notification((notification) => {
                                // New Message Sound (Incoming)
                                if(notification.type == 'App\\Notifications\\NewMessage' && notification.chat_id != this.chat_id){
                                    document.getElementById('chat-notification').play();
                                }
                                
                                // Typing Indicator Logic
                                if(notification.type == 'App\\Notifications\\UserTyping' && notification.chat_id == this.chat_id){
                                    this.isOtherUserTyping = true;
                                    clearTimeout(this.typingTimeout);
                                    this.typingTimeout = setTimeout(() => {
                                        this.isOtherUserTyping = false;
                                    }, 3000);
                                }
                            });

                        // Sent Message Sound
                        window.addEventListener('message-sent', () => {
                            document.getElementById('sent-notification').play();
                        });
                    },
                    checkScroll(e) {
                        const el = e.target;
                        // If user scrolls up more than 300px, show the button
                        this.showScrollBtn = (el.scrollHeight - el.scrollTop - el.clientHeight) > 300;
                    },
                    scrollToBottom() {
                        const final = document.getElementById('final');
                        if(final) final.scrollIntoView({ behavior: 'smooth', block: 'end' });
                    },
                    toggleSettings() {
                        @this.toggleSettings();
                    }
                }
            }
            Livewire.on('scrollIntoView', () => {
                setTimeout(() => {
                    const final = document.getElementById('final');
                    if(final) final.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }, 100);
            });
            
            // Listen for successful send to play sound
            window.addEventListener('scrollIntoView', () => {
                const audio = document.getElementById('sent-notification');
                if(audio) audio.play();
            });
        </script>
    @endpush
</div>
