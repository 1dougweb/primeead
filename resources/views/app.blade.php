<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kanban</title>
    <script src="https://unpkg.com/vue@3"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@inertiajs/inertia@0.11.0"></script>
    <script src="https://unpkg.com/@inertiajs/inertia-vue3@0.11.0"></script>
</head>
<body>
    <div id="app"></div>

    <script>
        const { createApp } = Vue;
        
        const KanbanBoard = {
            data() {
                return {
                    columns: [
                        {
                            id: 1,
                            title: 'To Do',
                            tasks: []
                        },
                        {
                            id: 2,
                            title: 'In Progress',
                            tasks: []
                        },
                        {
                            id: 3,
                            title: 'Done',
                            tasks: []
                        }
                    ]
                }
            },
            methods: {
                addTask(columnId) {
                    const title = prompt('Task name:');
                    if (!title) return;
                    
                    const column = this.columns.find(col => col.id === columnId);
                    if (column) {
                        column.tasks.push({
                            id: Date.now(),
                            title
                        });
                    }
                }
            },
            template: @verbatim`
                <div class="p-4">
                    <h1 class="text-2xl font-bold mb-4">Kanban Board</h1>
                    <div class="flex gap-4">
                        <div 
                            v-for="column in columns" 
                            :key="column.id"
                            class="w-80 bg-gray-100 rounded-lg p-4"
                        >
                            <h2 class="font-bold mb-4">{{ column.title }}</h2>
                            
                            <div class="space-y-2">
                                <div 
                                    v-for="task in column.tasks" 
                                    :key="task.id"
                                    class="bg-white p-3 rounded shadow"
                                >
                                    {{ task.title }}
                                </div>
                            </div>

                            <button 
                                @click="addTask(column.id)"
                                class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                            >
                                Add Task
                            </button>
                        </div>
                    </div>
                </div>
            `@endverbatim
        };

        createApp(KanbanBoard).mount('#app');
    </script>
</body>
</html>
