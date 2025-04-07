document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = 'http://api.cc.localhost';
    const api = new Api(API_BASE_URL);
    let allCourses = [];
    let categories = [];

    // DOM elements
    const categoriesList = document.getElementById('categories-list');
    const coursesGrid = document.getElementById('courses-grid');
    const currentCategoryTitle = document.getElementById('current-category');
    const allCoursesCount = document.getElementById('all-courses-count');
    const allCoursesItem = document.querySelector('[data-id="all"]');

    // Initialize the application
    async function init() {
        try {
            // Load data
            const [coursesData, categoriesData] = await Promise.all([
                api.getCourses(),
                api.getCategories()
            ]);

            allCourses = coursesData;
            categories = categoriesData;

            // Render UI
            renderCategories(categories);
            renderCourses(allCourses);

            // Update all courses count
            allCoursesCount.textContent = allCourses.length;
            
            // Add event listener for "All Courses" item
            if (allCoursesItem) {
                allCoursesItem.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectCategory('all', 'All Courses');
                });
            }

        } catch (error) {
            console.error('Failed to initialize app:', error);
            alert('Failed to load data. Please try again later.');
        }
    }

    init();

    function renderCategories(categories, parentElement = categoriesList, level = 0) {
        categories.forEach(category => {
            const li = document.createElement('li');
            li.className = `category-item ${level > 0 ? 'nested-category' : ''}`;
            li.dataset.id = category.id;

            li.innerHTML = `
                <span class="category-name">${category.name}</span>
                <span class="course-count">${category.count_of_courses}</span>
            `;

            li.addEventListener('click', (e) => {
                e.stopPropagation();
                selectCategory(category.id, category.name);
            });

            parentElement.appendChild(li);

            // Render children if any (up to depth 4)
            if (category.children && category.children.length > 0 && level < 3) {
                const ul = document.createElement('ul');
                ul.className = 'categories-list';
                parentElement.appendChild(ul);
                renderCategories(category.children, ul, level + 1);
            }
        });
    }

    function renderCourses(courses) {
        coursesGrid.innerHTML = '';

        if (courses.length === 0) {
            coursesGrid.innerHTML = '<p>No courses found in this category.</p>';
            return;
        }

        courses.forEach(course => {
            const courseCard = document.createElement('div');
            courseCard.className = 'course-card';

            courseCard.innerHTML = `
                <div class="course-header">
                <img src="${course.preview || 'default-image.jpg'}" alt="${course.name}" class="course-image">
                </div>
                <div class="course-content">
                    <span class="course-category">${course.main_category_name || ''}</span>
                    <h3 class="course-title">${course.name}</h3>
                    <p class="course-description">${course.description || 'No description available.'}</p>
                </div>
            `;

            coursesGrid.appendChild(courseCard);
        });
    }

    async function selectCategory(categoryId, categoryName) {
        // Update active class
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('active');
        });

        const selectedItem = categoryId === 'all'
            ? document.querySelector('[data-id="all"]')
            : document.querySelector(`[data-id="${categoryId}"]`);

        if (selectedItem) {
            selectedItem.classList.add('active');
        }

        // Update title
        currentCategoryTitle.textContent = categoryId === 'all' ? 'All Courses' : categoryName;

        try {
            // Get courses filtered by category
            const courses = categoryId === 'all'
                ? await api.getCourses()
                : await api.getCourses(categoryId);

            // Render filtered courses
            renderCourses(courses);
        } catch (error) {
            console.error('Failed to fetch courses:', error);
            alert('Failed to load courses. Please try again later.');
        }
    }
});