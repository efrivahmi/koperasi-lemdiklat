<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

const photoInput = ref(null);
const photoPreview = ref(null);

const form = useForm({
    photo: null,
});

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const uploadPhoto = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('profile.photo.upload'), {
        errorBag: 'uploadPhoto',
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            form.reset();
        },
    });
};

const deletePhoto = () => {
    if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
        form.delete(route('profile.photo.delete'), {
            preserveScroll: true,
            onSuccess: () => {
                photoPreview.value = null;
            },
        });
    }
};

const getPhotoUrl = () => {
    if (photoPreview.value) {
        return photoPreview.value;
    }

    if (user.photo) {
        return `/storage/${user.photo}`;
    }

    return null;
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Foto Profil
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Upload foto profil Anda. Ukuran maksimal 2MB.
            </p>
        </header>

        <div class="mt-6 space-y-6">
            <!-- Current Photo -->
            <div class="flex items-center space-x-6">
                <!-- Photo Preview -->
                <div class="relative">
                    <div v-if="getPhotoUrl()" class="w-32 h-32 rounded-full overflow-hidden border-4 border-purple-500 shadow-lg">
                        <img :src="getPhotoUrl()" alt="Profile Photo" class="w-full h-full object-cover">
                    </div>
                    <div v-else class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center border-4 border-purple-500 shadow-lg">
                        <span class="text-5xl text-white font-bold">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex-1">
                    <input
                        ref="photoInput"
                        type="file"
                        class="hidden"
                        accept="image/*"
                        @change="updatePhotoPreview"
                    />

                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            @click="selectNewPhoto"
                            class="px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-lg hover:from-purple-600 hover:to-indigo-700 transition duration-200 shadow-md"
                        >
                            Pilih Foto Baru
                        </button>

                        <button
                            v-if="user.photo"
                            type="button"
                            @click="deletePhoto"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-200 shadow-md disabled:opacity-50"
                        >
                            Hapus Foto
                        </button>
                    </div>

                    <p v-if="form.errors.photo" class="mt-2 text-sm text-red-600">
                        {{ form.errors.photo }}
                    </p>
                </div>
            </div>

            <!-- Upload Button (shown when new photo is selected) -->
            <div v-if="photoPreview" class="flex items-center space-x-2">
                <button
                    type="button"
                    @click="uploadPhoto"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-200 shadow-md disabled:opacity-50"
                >
                    <span v-if="form.processing">Mengupload...</span>
                    <span v-else>Upload Foto</span>
                </button>

                <button
                    type="button"
                    @click="photoPreview = null"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200 shadow-md disabled:opacity-50"
                >
                    Batal
                </button>
            </div>

            <!-- Success Message -->
            <div v-if="page.props.flash?.success" class="p-4 bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 rounded-lg">
                {{ page.props.flash.success }}
            </div>
        </div>
    </section>
</template>
