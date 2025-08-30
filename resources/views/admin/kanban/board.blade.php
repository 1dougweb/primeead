@foreach($columns as $column)
    <div class="kanban-column" data-column="{{ $column->slug }}">
        <div class="kanban-column-header bg-{{ $column->color }}">
            <h6 class="mb-0">
                @if($column->icon)
                    <span class="me-1">{{ $column->icon }}</span>
                @endif
                {{ $column->name }}
                <span class="badge bg-light text-dark ms-1">{{ $column->leads_count }}</span>
            </h6>
        </div>
        <div class="kanban-column-body">
            @foreach($column->leads as $lead)
                <!-- ... existing code ... -->
            @endforeach
        </div>
    </div>
@endforeach 