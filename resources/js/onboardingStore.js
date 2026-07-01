const ONBOARDING_STORAGE_KEY = 'onboarding_data';

export const onboardingStore = {
    saveStep(step, data) {
        const stored = this.getAll();
        stored[`step_${step}`] = data;
        stored.currentStep = step;
        localStorage.setItem(ONBOARDING_STORAGE_KEY, JSON.stringify(stored));
    },

    getStep(step) {
        const stored = this.getAll();
        return stored[`step_${step}`] || null;
    },

    update(data) {
        const stored = this.getAll();
        const currentStep = stored.currentStep || 1;
        stored[`step_${currentStep}`] = { ...stored[`step_${currentStep}`], ...data };
        localStorage.setItem(ONBOARDING_STORAGE_KEY, JSON.stringify(stored));
    },

    getAll() {
        try {
            const data = localStorage.getItem(ONBOARDING_STORAGE_KEY);
            return data ? JSON.parse(data) : {};
        } catch (e) {
            return {};
        }
    },

    clear() {
        localStorage.removeItem(ONBOARDING_STORAGE_KEY);
    },

    hasData() {
        const stored = this.getAll();
        return stored.currentStep !== undefined;
    },

    getCurrentStep() {
        return this.getAll().currentStep || 1;
    },

    getClinicData() {
        return this.getStep(1) || {};
    },

    getAdminData() {
        return this.getStep(2) || {};
    },

    getFullPayload() {
        const stored = this.getAll();
        const clinic = stored.step_1 || {};
        const admin = stored.step_2 || {};
        return {
            clinic_name: clinic.clinic_name || '',
            clinic_nit: clinic.clinic_nit || '',
            clinic_phone: clinic.clinic_phone || '',
            clinic_email: clinic.clinic_email || '',
            clinic_city: clinic.clinic_city || '',
            clinic_address: clinic.clinic_address || '',
            admin_name: admin.admin_name || '',
            admin_apellido: admin.admin_apellido || '',
            admin_document: admin.admin_document || '',
            admin_licencia: admin.admin_licencia || '',
            admin_email: admin.admin_email || '',
            admin_phone: admin.admin_phone || '',
            admin_password: admin.admin_password || '',
            admin_password_confirmation: admin.admin_password_confirmation || '',
        };
    }
};

window.onboardingStore = onboardingStore;