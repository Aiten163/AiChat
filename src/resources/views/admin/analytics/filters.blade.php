<div class="row mb-4">
    <div class="col-md-12">
        <form method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label for="start_date" class="mr-2">С:</label>
                <input type="date"
                       name="start_date"
                       id="start_date"
                       value="{{ $filters['start_date'] }}"
                       class="form-control">
            </div>
            <div class="form-group mr-3">
                <label for="end_date" class="mr-2">По:</label>
                <input type="date"
                       name="end_date"
                       id="end_date"
                       value="{{ $filters['end_date'] }}"
                       class="form-control">
            </div>
            <button type="submit" class="btn btn-primary m-3">Применить</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary ml-2">Сбросить</a>
        </form>
    </div>
</div>
