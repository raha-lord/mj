<div>
    <label for="name" class="block font-medium text-sm text-gray-700">{{ 'Name' }}</label>
    <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" id="name" name="name" type="text" value="{{ isset($task->name) ? $task->name : ''}}" >
    {!! $errors->first('name', '<p>:message</p>') !!}
</div>
<div>
    <label for="description" class="block font-medium text-sm text-gray-700">{{ 'Description' }}</label>
    <textarea id="description" name="description" type="textarea" rows="5" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" >{{ isset($task->description) ? $task->description : ''}}</textarea>

    {!! $errors->first('description', '<p>:message</p>') !!}
</div>
<div>
    <label for="d_start" class="block font-medium text-sm text-gray-700">{{ 'D Start' }}</label>
    <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" id="d_start" name="d_start" type="datetime-local" value="{{ isset($task->d_start) ? $task->d_start : ''}}" >
    {!! $errors->first('d_start', '<p>:message</p>') !!}
</div>
<div>
    <label for="d_end" class="block font-medium text-sm text-gray-700">{{ 'D End' }}</label>
    <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" id="d_end" name="d_end" type="datetime-local" value="{{ isset($task->d_end) ? $task->d_end : ''}}" >
    {!! $errors->first('d_end', '<p>:message</p>') !!}
</div>
<div>
    <label for="d_close" class="block font-medium text-sm text-gray-700">{{ 'D Close' }}</label>
    <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" id="d_close" name="d_close" type="datetime-local" value="{{ isset($task->d_close) ? $task->d_close : ''}}" >
    {!! $errors->first('d_close', '<p>:message</p>') !!}
</div>
<div class="form-group">
    <label for="status_id">Статус</label>
    <select name="status_id" id="status_id" class="form-control">
        <option value="">-- Выберите статус --</option>
        @foreach ($statuses as $status)
            <option value="{{ $status->id }}" {{ old('status_id', $task->status_id ?? '') == $status->id ? 'selected' : '' }}>
                {{ $status->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="project_id">Проект</label>
    <select name="project_id" id="project_id" class="form-control">
        <option value="">-- Выберите проект --</option>
        @foreach ($projects as $project)
            <option value="{{ $project->id }}" {{ old('project_id', $task->project_id ?? '') == $project->id ? 'selected' : '' }}>
                {{ $project->name }}
            </option>
        @endforeach
    </select>
</div>


<div class="flex items-center gap-4">
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
        {{ $formMode === 'edit' ? 'Update' : 'Create' }}
    </button>
</div>
