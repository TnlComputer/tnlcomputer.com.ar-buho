<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use StoresUploads;

    public function index(): View
    {
        return view('admin.projects.index', ['projects' => Project::orderBy('sort')->get()]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => new Project(['active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, new Project());

        return redirect()->route('admin.projects.index')->with('status', 'Proyecto creado.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->save($request, $project);

        return redirect()->route('admin.projects.index')->with('status', 'Proyecto actualizado.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->deleteUpload($project->image);
        $project->delete();

        return back()->with('status', 'Proyecto eliminado.');
    }

    private function save(Request $request, Project $project): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'title_en' => ['nullable', 'string', 'max:160'],
            'kind' => ['nullable', 'string', 'max:60'],
            'kind_en' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:3000'],
            'description_en' => ['nullable', 'string', 'max:3000'],
            'tags' => ['nullable', 'string', 'max:300'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'code_url' => ['nullable', 'url', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:2100'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['tags'] = collect(explode(',', (string) $data['tags']))->map(fn ($t) => trim($t))->filter()->values()->all();
        $data['image'] = $this->storeUpload($request->file('photo'), 'proyectos', $project->image);
        $data['featured'] = $request->boolean('featured');
        $data['active'] = $request->boolean('active');
        $data['sort'] ??= 0;
        unset($data['photo']);

        $project->fill($data)->save();
    }
}
