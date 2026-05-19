<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, nextTick } from 'vue';
import { gsap } from 'gsap';

const props = defineProps({
    status: String,
});

const digits = ref(['', '', '', '', '', '']);
const inputs = ref([]);

const form = useForm({
    code: '',
});

const handleInput = (index, event) => {
    const val = event.target.value;
    
    // Allow only single digits
    if (!/^\d*$/.test(val)) {
        digits.value[index] = '';
        return;
    }
    
    digits.value[index] = val.slice(-1);
    
    if (digits.value[index] !== '' && index < 5) {
        nextTick(() => {
            inputs.value[index + 1]?.focus();
        });
    }
    
    updateCodeAndSubmit();
};

const handleKeyDown = (index, event) => {
    if (event.key === 'Backspace') {
        if (digits.value[index] === '' && index > 0) {
            digits.value[index - 1] = '';
            nextTick(() => {
                inputs.value[index - 1]?.focus();
            });
        } else {
            digits.value[index] = '';
        }
        updateCodeAndSubmit();
    } else if (event.key === 'ArrowLeft' && index > 0) {
        nextTick(() => {
            inputs.value[index - 1]?.focus();
        });
    } else if (event.key === 'ArrowRight' && index < 5) {
        nextTick(() => {
            inputs.value[index + 1]?.focus();
        });
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const paste = event.clipboardData.getData('text');
    if (!/^\d{6}$/.test(paste)) return;
    
    const splitData = paste.split('');
    for (let i = 0; i < 6; i++) {
        digits.value[i] = splitData[i] || '';
    }
    
    nextTick(() => {
        inputs.value[5]?.focus();
        updateCodeAndSubmit();
    });
};

const updateCodeAndSubmit = () => {
    form.code = digits.value.join('');
    if (form.code.length === 6) {
        submit();
    }
};

const resendForm = useForm({});
const resendCode = () => {
    resendForm.post(route('login.two-factor.resend'), {
        onSuccess: () => {
            // Shake the input panel on code reset
            gsap.fromTo(".code-box", 
                { x: -5 }, 
                { x: 5, duration: 0.05, repeat: 5, yoyo: true, ease: "sine.inOut" }
            );
        }
    });
};

const submit = () => {
    form.post(route('login.two-factor'), {
        onError: () => {
            // Vibrate screen/form on error
            gsap.fromTo(".login-panel", 
                { x: -10 }, 
                { x: 10, duration: 0.05, repeat: 5, yoyo: true, ease: "sine.inOut" }
            );
        }
    });
};

onMounted(() => {
    // Focus first input automatically
    inputs.value[0]?.focus();

    // GSAP animations
    gsap.from(".game-bg", { duration: 1.2, opacity: 0, ease: "power2.out" });
    
    gsap.from(".login-panel", { 
        duration: 0.8, 
        x: -150, 
        opacity: 0, 
        ease: "back.out(1.2)" 
    });

    gsap.from(".pop-text", {
        duration: 0.6,
        scale: 0.5,
        opacity: 0,
        stagger: 0.15,
        ease: "back.out(1.5)",
        delay: 0.3
    });

    gsap.to(".logo-float", {
        y: -15,
        duration: 2.5,
        repeat: -1,
        yoyo: true,
        ease: "sine.inOut"
    });
});
</script>

<template>
    <Head title="CityPlay - Double Authentification" />

    <component :is="'style'">
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap');
        .font-gaming {
            font-family: 'Fredoka', sans-serif;
        }
    </component>

    <div class="game-bg min-h-screen bg-gradient-to-b from-[#4fc3f7] to-[#1976d2] flex items-center justify-start p-4 md:p-0 overflow-hidden font-gaming relative select-none">
        
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:3rem_3rem] pointer-events-none"></div>

        <div class="absolute top-1/4 right-1/4 w-[600px] h-[600px] bg-white/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 text-8xl opacity-10 text-white font-black tracking-widest uppercase">CITYPLAY</div>

        <div class="w-full min-h-screen grid grid-cols-1 lg:grid-cols-12 relative z-10">
            
            <div class="lg:col-span-5 bg-white p-8 md:p-12 flex flex-col justify-center shadow-2xl border-r-8 border-blue-600/20 login-panel h-full">
                
                <div class="text-center mb-8">
                    <div class="inline-block transform -rotate-2 text-5xl font-black tracking-wider text-blue-600 drop-shadow-[0_5px_0_rgba(29,78,216,1)] uppercase">
                        <span class="text-yellow-400 drop-shadow-[0_5px_0_rgba(234,179,8,1)]">City</span>Play
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 mt-6 tracking-wide leading-none">
                        Double <br>
                        <span class="text-blue-600">Vérification 2FA</span>
                    </h1>
                    <p class="text-slate-500 font-bold text-sm mt-3 px-4">
                        Un code de sécurité à 6 chiffres a été envoyé par e-mail. Veuillez le renseigner ci-dessous.
                    </p>
                </div>

                <!-- Status / Alert banners -->
                <div v-if="status" class="mb-6 px-6 py-4 bg-green-50 border-2 border-green-200 text-green-600 rounded-2xl text-xs font-bold flex items-center space-x-2 shadow-sm">
                    <span class="text-base">📬</span>
                    <span>{{ status }}</span>
                </div>

                <div v-if="form.errors.code" class="mb-6 px-6 py-4 bg-red-50 border-2 border-red-200 text-red-600 rounded-2xl text-xs font-bold flex items-center space-x-2 shadow-sm">
                    <span class="text-base">⚠️</span>
                    <span>{{ form.errors.code }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- 6 Digits Inputs Horizontal Row -->
                    <div class="flex flex-row justify-center space-x-2 md:space-x-3">
                        <input
                            v-for="(digit, idx) in digits"
                            :key="idx"
                            ref="inputs"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            v-model="digits[idx]"
                            @input="handleInput(idx, $event)"
                            @keydown="handleKeyDown(idx, $event)"
                            @paste="handlePaste"
                            class="code-box w-11 h-11 md:w-14 md:h-14 text-center font-black text-2xl md:text-3xl bg-slate-50 border-3 border-slate-200 rounded-2xl focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 text-slate-800 shadow-sm transition-all"
                        />
                    </div>

                    <div class="pt-4 space-y-4">
                        <button 
                            type="submit"
                            :disabled="form.processing || form.code.length !== 6"
                            class="w-full py-4 bg-yellow-400 hover:bg-yellow-300 disabled:opacity-50 disabled:cursor-not-allowed text-slate-900 font-black text-xl rounded-full border-b-4 border-yellow-600 active:border-b-0 active:translate-y-1 active:shadow-none shadow-md transition-all uppercase tracking-wide flex items-center justify-center space-x-2"
                        >
                            <span>Valider le Code</span>
                        </button>

                        <div class="text-center">
                            <button
                                type="button"
                                @click="resendCode"
                                :disabled="resendForm.processing"
                                class="text-sm font-black text-blue-600 hover:text-blue-700 hover:underline focus:outline-none"
                            >
                                {{ resendForm.processing ? 'Envoi en cours...' : 'Renvoyer un nouveau code' }}
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-8 text-center border-t-2 border-slate-100 pt-6">
                    <p class="text-sm font-bold text-slate-500">
                        Retourner à la 
                        <Link :href="route('login')" class="text-blue-600 hover:text-blue-700 underline underline-offset-4 ml-1">
                            connexion
                        </Link>
                    </p>
                </div>
            </div>

            <div class="hidden lg:flex lg:col-span-7 flex-col items-center justify-center p-12 text-center relative h-full">
                
                <div class="logo-float w-64 h-64 rounded-3xl overflow-hidden shadow-[0_25px_60px_rgba(234,179,8,0.4)] border-4 border-white mb-12">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="100%" height="100%">
                        <rect width="300" height="300" rx="12" fill="#FFC107" />
                        <g transform="translate(60, 75) scale(4.5)">
                            <g fill="none" stroke="#1565C0" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M 0,33 L 0,26 L 6,23 L 11,25 L 11,33" />
                                <line x1="3" y1="27" x2="3" y2="28" />
                                <line x1="3" y1="30" x2="3" y2="31" />
                                <line x1="8" y1="27" x2="8" y2="28" />
                                <line x1="8" y1="30" x2="8" y2="31" />

                                <path d="M 11,33 L 11,4 L 20,0 L 26,2 L 26,33" />
                                <line x1="15" y1="6" x2="15" y2="7" />
                                <line x1="15" y1="10" x2="15" y2="11" />
                                <line x1="15" y1="14" x2="15" y2="15" />
                                <line x1="15" y1="18" x2="15" y2="19" />
                                <line x1="15" y1="22" x2="15" y2="23" />
                                <line x1="15" y1="26" x2="15" y2="27" />

                                <line x1="21" y1="7" x2="21" y2="8" />
                                <line x1="21" y1="11" x2="21" y2="12" />
                                <line x1="21" y1="15" x2="21" y2="16" />
                                <line x1="21" y1="19" x2="21" y2="20" />
                                <line x1="21" y1="23" x2="21" y2="24" />
                                <line x1="21" y1="27" x2="21" y2="28" />

                                <path d="M 26,33 L 26,14 L 33,10 L 41,13 L 41,29" />
                                <line x1="31" y1="15" x2="31" y2="16" />
                                <line x1="31" y1="19" x2="31" y2="20" />
                                <line x1="31" y1="23" x2="21" y2="24" />
                                
                                <line x1="36" y1="17" x2="36" y2="18" />
                                <line x1="36" y1="21" x2="36" y2="22" />
                                <line x1="36" y1="25" x2="36" y2="26" />
                            </g>
                        </g>
                    </svg>
                </div>

                <div class="space-y-3 pointer-events-none select-none">
                    <div class="pop-text text-7xl font-black text-white tracking-wide uppercase drop-shadow-[0_6px_0_#1d4ed8]">
                        PROTECT
                    </div>
                    <div class="pop-text text-6xl font-black text-yellow-400 tracking-wide uppercase drop-shadow-[0_5px_0_#b45309]">
                        YOUR ACCOUNT
                    </div>
                    <div class="pop-text text-2xl font-bold text-cyan-100 tracking-widest uppercase opacity-90">
                        Entering verification challenge
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.game-bg::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.04) 50%);
    background-size: 100% 4px;
    pointer-events: none;
    z-index: 1;
}
.border-3 {
    border-width: 3px !important;
}
</style>
