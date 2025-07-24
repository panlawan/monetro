<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                <i class="fas fa-target text-primary me-2"></i>Financial Goals
            </h2>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                <i class="fas fa-plus me-1"></i>Add Goal
            </button>
        </div>
    </x-slot>

    <!-- Finance Navigation -->
    @include('layouts.finance-nav')

    <!-- Active Goals -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="fw-bold text-primary mb-0">Active Goals</h6>
        </div>
        <div class="card-body">
            @if($activeGoals->count() > 0)
                <div class="row">
                    @foreach($activeGoals as $goal)
                        <div class="col-xl-4 col-lg-6 mb-4">
                            <div class="card border-start border-{{ $goal->is_overdue ? 'warning' : 'primary' }} border-4 h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title text-primary">
                                                <i class="fas {{ $goal->icon }} me-2"></i>{{ $goal->name }}
                                            </h5>
                                            @if($goal->description)
                                                <p class="text-muted small">{{ $goal->description }}</p>
                                            @endif
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="addProgress({{ $goal->id }})">
                                                    <i class="fas fa-plus me-2"></i>Add Progress
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-success" href="#" onclick="markCompleted({{ $goal->id }})">
                                                    <i class="fas fa-check me-2"></i>Mark Completed
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteGoal({{ $goal->id }})">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small fw-bold">Progress</span>
                                            <span class="small fw-bold">{{ number_format($goal->progress_percentage, 1) }}%</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" 
                                                 style="width: {{ $goal->progress_percentage }}%; background-color: {{ $goal->color }};"
                                                 role="progressbar"></div>
                                        </div>
                                    </div>

                                    <!-- Amount Progress -->
                                    <div class="row text-center mb-3">
                                        <div class="col">
                                            <div class="small text-muted">Current</div>
                                            <div class="fw-bold">฿{{ $goal->formatted_current_amount }}</div>
                                        </div>
                                        <div class="col">
                                            <div class="small text-muted">Target</div>
                                            <div class="fw-bold">฿{{ $goal->formatted_target_amount }}</div>
                                        </div>
                                        <div class="col">
                                            <div class="small text-muted">Remaining</div>
                                            <div class="fw-bold text-{{ $goal->remaining_amount > 0 ? 'warning' : 'success' }}">
                                                ฿{{ $goal->formatted_remaining_amount }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Target Date -->
                                    <div class="text-center">
                                        <small class="text-muted">
                                            Target Date: {{ $goal->target_date->format('M d, Y') }}
                                            @if($goal->is_overdue)
                                                <span class="badge bg-warning ms-1">Overdue</span>
                                            @else
                                                <span class="badge bg-info ms-1">{{ $goal->days_remaining }} days left</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-target fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No active goals</h5>
                    <p class="text-muted">Create your first financial goal to start tracking your progress.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGoalModal">
                        <i class="fas fa-plus me-1"></i>Create Goal
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Completed Goals -->
    @if($completedGoals->count() > 0)
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="fw-bold text-success mb-0">Recently Completed Goals</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Goal</th>
                                <th>Target Amount</th>
                                <th>Completed Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedGoals as $goal)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle p-2 text-white" style="background-color: {{ $goal->color }};">
                                                    <i class="fas {{ $goal->icon }}"></i>
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-bold">{{ $goal->name }}</div>
                                                @if($goal->description)
                                                    <small class="text-muted">{{ $goal->description }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">฿{{ $goal->formatted_target_amount }}</span>
                                    </td>
                                    <td>
                                        {{ $goal->updated_at->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $goal->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteGoal({{ $goal->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Add Goal Modal -->
    <div class="modal fade" id="addGoalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addGoalForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Goal Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Target Amount</label>
                                <input type="number" name="target_amount" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="col">
                                <label class="form-label">Current Amount</label>
                                <input type="number" name="current_amount" class="form-control" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Target Date</label>
                            <input type="date" name="target_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Color</label>
                                <select name="color" class="form-select">
                                    <option value="#1cc88a">Green</option>
                                    <option value="#36b9cc">Blue</option>
                                    <option value="#f6c23e">Yellow</option>
                                    <option value="#e74a3b">Red</option>
                                    <option value="#858796">Gray</option>
                                    <option value="#5a5c69">Dark</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Icon</label>
                                <select name="icon" class="form-select">
                                    <option value="fa-target">Target</option>
                                    <option value="fa-home">Home</option>
                                    <option value="fa-car">Car</option>
                                    <option value="fa-graduation-cap">Education</option>
                                    <option value="fa-plane">Travel</option>
                                    <option value="fa-heart">Health</option>
                                    <option value="fa-piggy-bank">Savings</option>
                                    <option value="fa-ring">Wedding</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Goal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Progress Modal -->
    <div class="modal fade" id="addProgressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addProgressForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Goal</label>
                            <input type="text" id="progressGoalName" class="form-control" readonly>
                            <input type="hidden" id="progressGoalId" name="goal_id">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Amount to Add</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Note (Optional)</label>
                            <input type="text" name="note" class="form-control" placeholder="e.g., Monthly savings deposit">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Progress</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Add Progress function
    function addProgress(goalId) {
        const goal = @json($activeGoals).find(g => g.id === goalId);
        if (goal) {
            document.getElementById('progressGoalName').value = goal.name;
            document.getElementById('progressGoalId').value = goalId;
            new bootstrap.Modal(document.getElementById('addProgressModal')).show();
        }
    }

    // Mark Completed function
    function markCompleted(goalId) {
        if (confirm('Mark this goal as completed?')) {
            fetch(`/finance/goals/${goalId}/complete`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error marking goal as completed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error marking goal as completed');
            });
        }
    }

    // Delete Goal function
    function deleteGoal(goalId) {
        if (confirm('Are you sure you want to delete this goal?')) {
            fetch(`/finance/goals/${goalId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting goal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting goal');
            });
        }
    }

    // Handle Add Goal form
    document.getElementById('addGoalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/finance/goals', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error creating goal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating goal');
        });
    });

    // Handle Add Progress form
    document.getElementById('addProgressForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const goalId = formData.get('goal_id');
        
        fetch(`/finance/goals/${goalId}/progress`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error adding progress');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding progress');
        });
    });
    </script>
</x-app-layout>