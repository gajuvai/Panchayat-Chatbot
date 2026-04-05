@extends('layouts.app')
@section('title', 'Panchayat Chatbot')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border flex flex-col" style="height: 600px;" x-data="chatApp()">

        {{-- Header --}}
        <div class="bg-indigo-700 text-white px-5 py-4 rounded-t-xl flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-lg">P</div>
            <div>
                <div class="font-semibold">Panchayat Assistant</div>
                <div class="text-indigo-200 text-xs flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span> Online
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="messageArea" x-ref="messageArea">
            {{-- Bot greeting --}}
            <div class="flex items-start gap-2">
                <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-sm flex-shrink-0">🤖</div>
                <div class="bg-gray-100 rounded-xl rounded-tl-none px-4 py-2.5 max-w-sm text-sm text-gray-800">
                    <p>Hello, <strong>{{ auth()->user()->name }}</strong>! 👋</p>
                    <p class="mt-1">How can I help you today? Choose an option or type your question.</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <button @click="sendQuick('File a complaint')" class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-full hover:bg-indigo-700 transition">📋 File Complaint</button>
                        <button @click="sendQuick('Track my complaint status')" class="bg-white border text-gray-700 text-xs px-3 py-1.5 rounded-full hover:bg-gray-50 transition">🔍 Track Status</button>
                        <button @click="sendQuick('Show me upcoming events')" class="bg-white border text-gray-700 text-xs px-3 py-1.5 rounded-full hover:bg-gray-50 transition">📅 Events</button>
                        <button @click="sendQuick('Show me rules')" class="bg-white border text-gray-700 text-xs px-3 py-1.5 rounded-full hover:bg-gray-50 transition">📖 Rules</button>
                    </div>
                </div>
            </div>

            {{-- Dynamic messages --}}
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.sender === 'user' ? 'flex flex-row-reverse items-start gap-2' : 'flex items-start gap-2'">
                    <div :class="msg.sender === 'user' ? 'w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0' : 'w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-sm flex-shrink-0'">
                        <span x-text="msg.sender === 'user' ? '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}' : '🤖'"></span>
                    </div>
                    <div :class="msg.sender === 'user' ? 'bg-indigo-600 text-white rounded-xl rounded-tr-none px-4 py-2.5 max-w-sm text-sm' : 'bg-gray-100 text-gray-800 rounded-xl rounded-tl-none px-4 py-2.5 max-w-sm text-sm'">
                        <p x-html="msg.text"></p>
                        <div x-show="msg.quickReplies && msg.quickReplies.length" class="flex flex-wrap gap-1.5 mt-2">
                            <template x-for="qr in msg.quickReplies || []">
                                <template x-if="qr.action === 'navigate'">
                                    <a :href="qr.url" class="bg-white border text-gray-700 text-xs px-3 py-1.5 rounded-full hover:bg-gray-50 transition" x-text="qr.text"></a>
                                </template>
                                <template x-if="qr.action !== 'navigate'">
                                    <button @click="sendQuick(qr.text)" class="bg-white border text-gray-700 text-xs px-3 py-1.5 rounded-full hover:bg-gray-50 transition" x-text="qr.text"></button>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="typing" class="flex items-start gap-2">
                <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-sm">🤖</div>
                <div class="bg-gray-100 rounded-xl rounded-tl-none px-4 py-3">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0s"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0.15s"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0.3s"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t p-3 flex items-center gap-2">
            <input type="text" x-model="input" @keydown.enter="send()"
                placeholder="Type a message..."
                class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button @click="send()" :disabled="!input.trim() || sending"
                class="bg-indigo-600 text-white rounded-full w-9 h-9 flex items-center justify-center hover:bg-indigo-700 disabled:opacity-50 transition flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
function chatApp() {
    return {
        input: '',
        messages: [],
        typing: false,
        sending: false,
        sessionId: null,

        async init() {
            const res = await fetch('{{ route("chat.session") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
            });
            const data = await res.json();
            this.sessionId = data.session_id;
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.sending) return;
            this.input = '';
            this.messages.push({ id: Date.now(), sender: 'user', text, quickReplies: [] });
            this.scrollDown();
            this.sending = true;
            this.typing = true;

            try {
                const res = await fetch('{{ route("chat.message") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ message: text, session_id: this.sessionId })
                });
                const data = await res.json();
                this.typing = false;
                this.messages.push({ id: Date.now() + 1, sender: 'bot', text: data.message, quickReplies: data.quick_replies || [] });
                this.scrollDown();
            } catch(e) {
                this.typing = false;
                this.messages.push({ id: Date.now() + 1, sender: 'bot', text: 'Sorry, something went wrong. Please try again.', quickReplies: [] });
            }
            this.sending = false;
        },

        sendQuick(text) {
            this.input = text;
            this.send();
        },

        scrollDown() {
            this.$nextTick(() => {
                const el = this.$refs.messageArea;
                if (el) el.scrollTop = el.scrollHeight;
            });
        }
    }
}
</script>
@endsection
