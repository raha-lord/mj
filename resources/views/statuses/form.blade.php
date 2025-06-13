<div>
    <label for="slug" class="block font-medium text-sm text-gray-700">{{ 'Slug' }}</label>
    <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" id="slug" name="slug" type="text" value="{{ isset($statuscode->slug) ? $statuscode->slug : ''}}" >
    {!! $errors->first('slug', '<p>:message</p>') !!}
</div>
<div>
    <label for="name" class="block font-medium text-sm text-gray-700">{{ 'Name' }}</label>
    <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" id="name" name="name" type="text" value="{{ isset($statuscode->name) ? $statuscode->name : ''}}" >
    {!! $errors->first('name', '<p>:message</p>') !!}
</div>
<div>
    <label for="description" class="block font-medium text-sm text-gray-700">{{ 'Description' }}</label>
    <textarea id="description" name="description" type="textarea" rows="5" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" >{{ isset($statuscode->description) ? $statuscode->description : ''}}</textarea>

    {!! $errors->first('description', '<p>:message</p>') !!}
</div>


<div class="flex items-center gap-4">
    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
        {{ $formMode === 'edit' ? 'Update' : 'Create' }}
    </button>
</div>
