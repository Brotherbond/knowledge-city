class Api {
    constructor(baseUrl) {
        this.baseUrl = baseUrl || '/api';
    }

    async get(endpoint) {
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}`);
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    // Specific API methods
    async getCategories() {
        return this.get('categories');
    }

    async getCategoryById(id) {
        return this.get(`categories/${id}`);
    }

    async getCourses(categoryId = null) {
        const endpoint = categoryId ? `courses?category_id=${categoryId}` : 'courses';
        return this.get(endpoint);
    }

    async getCourseById(id) {
        return this.get(`courses/${id}`);
    }
}