<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(string $resource): View
    {
        $config = $this->resourceConfig($resource);
        $items = DB::table($config['table'])->orderBy($config['order_by'] ?? 'name')->get();

        return view('master-data.index', [
            'resource' => $resource,
            'config' => $config,
            'items' => $items,
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = $this->resourceConfig($resource);
        $data = $request->validate($config['rules'], $config['messages'] ?? []);

        $payload = $this->payloadFromData($config, $data);
        
        if ($config['table'] === 'users' && isset($payload['password'])) {
            $payload['password'] = bcrypt($payload['password']);
        }

        DB::table($config['table'])->insert([
            ...$payload,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('master-data.index', $resource)
            ->with('status', $config['label'].' berhasil ditambahkan.');
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = $this->resourceConfig($resource, $id);
        $data = $request->validate($config['rules'], $config['messages'] ?? []);

        $payload = $this->payloadFromData($config, $data);

        if ($config['table'] === 'users') {
            if (!empty($payload['password'])) {
                $payload['password'] = bcrypt($payload['password']);
            } else {
                unset($payload['password']);
            }
        }

        DB::table($config['table'])
            ->where('id', $id)
            ->update([
                ...$payload,
                'updated_at' => now(),
            ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $config['label'] . ' berhasil diperbarui.'
            ]);
        }

        return redirect()
            ->route('master-data.index', $resource)
            ->with('status', $config['label'] . ' berhasil diperbarui.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $config = $this->resourceConfig($resource);

        DB::table($config['table'])->where('id', $id)->delete();

        return redirect()
            ->route('master-data.index', $resource)
            ->with('status', $config['label'].' berhasil dihapus.');
    }

    public static function resources(): array
    {
        return [
            'customers' => ['label' => 'Customer', 'table' => 'customers', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah customer']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'portofolios' => ['label' => 'Portofolio', 'table' => 'portofolios', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah portofolio']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'programs' => ['label' => 'Program', 'table' => 'programs', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah program']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'execution-types' => ['label' => 'Jenis Eksekusi', 'table' => 'execution_types', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah jenis eksekusi']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'branches' => ['label' => 'Branch', 'table' => 'branches', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah branch']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'pm-projects' => ['label' => 'PM Project', 'table' => 'pm_projects', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah PM project']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'waspangs' => ['label' => 'Waspang', 'table' => 'waspangs', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah waspang']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'mitras' => ['label' => 'Mitra', 'table' => 'mitras', 'order_by' => 'name', 'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Tambah mitra']], 'rules' => ['name' => ['required', 'string', 'max:255']]],
            'users' => [
                'label' => 'Pengguna',
                'table' => 'users',
                'order_by' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama lengkap'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'Alamat email'],
                    ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'placeholder' => 'Password (kosongkan jika tidak diubah)'],
                    ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'options' => ['admin' => 'Admin', 'warehouse' => 'Warehouse', 'finance' => 'Finance', 'procurement' => 'Procurement', 'konstruksi' => 'Konstruksi', 'commerce' => 'Commerce']],
                ],
                'rules' => [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'password' => ['nullable', 'string', 'min:8'],
                    'role' => ['required', 'string', 'in:admin,warehouse,finance,procurement,konstruksi,commerce'],
                ]
            ],
        ];
    }

    private function resourceConfig(string $resource, ?int $id = null): array
    {
        $resources = self::resources();

        abort_unless(isset($resources[$resource]), 404);

        $config = $resources[$resource];

        if ($resource === 'users' && $id) {
            $config['rules']['email'] = ['required', 'email', 'max:255', 'unique:users,email,'.$id];
        } elseif ($resource === 'users') {
            $config['rules']['email'] = ['required', 'email', 'max:255', 'unique:users,email'];
        }

        return $config + [
            'title' => Str::headline(str_replace('-', ' ', $resource)),
        ];
    }

    private function payloadFromData(array $config, array $data): array
    {
        $payload = [];

        foreach ($config['fields'] as $field) {
            $name = $field['name'];
            $payload[$name] = is_string($data[$name] ?? null) ? trim($data[$name]) : ($data[$name] ?? null);
        }

        return $payload;
    }
}
