<template>
    <div class="flex w-full h-full items-center justify-center">
        <div class="flex flex-col items-center text-center space-y-3 p-4 ">
            <!-- Gambar -->
            <img src="/images/empty_list.png" alt="empty-image" class="object-contain w-32 h-32 opacity-90" />

            <!-- Teks utama -->
            <span class="text-lg font-semibold text-gray-600">
                {{ title }}
            </span>

            <!-- Teks deskripsi kecil -->
            <span class="text-sm text-gray-400 max-w-[220px] leading-relaxed">
                {{ description }}
            </span>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    props: {
        title: { type: String, required: false, default: "Belum ada data" },
        description: { type: String, required: false, default: "Data akan muncul di sini setelah anda menambahkan item atau transaksi baru" }
    }
})
</script>