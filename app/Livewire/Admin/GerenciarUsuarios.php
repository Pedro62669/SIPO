<?php

namespace App\Livewire\Admin;

use App\Models\Unidade;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GerenciarUsuarios extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editingUserId = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $nome_secretario = '';
    public ?int $unidade_id = null;
    public string $password = '';
    public string $password_confirmation = '';
    public string $search = '';

    protected function rules(): array
    {
        $uniqueEmail = $this->editingUserId
            ? 'unique:users,email,'.$this->editingUserId
            : 'unique:users,email';

        $passwordRules = $this->editingUserId
            ? 'nullable|min:6|confirmed'
            : 'required|min:6|confirmed';

        return [
            'name' => 'required|string|max:255',
            'email' => "required|email|{$uniqueEmail}",
            'phone' => 'nullable|string|max:20',
            'nome_secretario' => 'nullable|string|max:255',
            'unidade_id' => 'required|exists:unidades,id',
            'password' => $passwordRules,
        ];
    }

    protected $validationAttributes = [
        'name' => 'nome',
        'email' => 'e-mail',
        'phone' => 'telefone',
        'nome_secretario' => 'nome do secretário',
        'unidade_id' => 'secretaria',
        'password' => 'senha',
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->nome_secretario = $user->nome_secretario ?? '';
        $this->unidade_id = $user->unidade_id;
        $this->password = '';
        $this->password_confirmation = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'nome_secretario' => $this->nome_secretario ?: null,
            'unidade_id' => $this->unidade_id,
            'active' => true,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update($data);
        } else {
            $user = User::create($data);
            $user->assignRole('usuario');
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('message', $this->editingUserId ? 'Usuário atualizado.' : 'Usuário criado.');
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['active' => !$user->active]);
    }

    public function excluirUsuario(int $userId): void
    {
        if (! auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        if (auth()->id() === $userId) {
            session()->flash('error', 'Não é possível excluir o próprio usuário.');

            return;
        }

        $user = User::role('usuario')->findOrFail($userId);
        $nome = $user->name;
        $user->delete();

        session()->flash('message', "Usuário \"{$nome}\" excluído com sucesso.");
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->nome_secretario = '';
        $this->unidade_id = null;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::role('usuario')
            ->with('unidade')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.gerenciar-usuarios', [
            'users' => $users,
            'unidades' => Unidade::orderBy('codigo')->get(),
        ]);
    }
}
