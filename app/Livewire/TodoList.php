<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TodoList extends Component
{
    use WithPagination;
    #[Rule('required|min:3|max:50')]
    public $name;

    public $search;

    public $editingTodoID;

    #[Rule('required|min:3|max:50')]
    public $editingTodoName;

    public function create(){
        // dd('test'); C'était juste pour voir si la fonction marche
        //La liste de ce que doit faire la fonction
        /* Validate
            Create the todo
            clear the input
            send flash message*/
            $validated = $this->ValidateOnly('name');
            Todo::create($validated);
            $this->reset('name');
            session()->flash('success','Created');
            $this -> resetPage();
    }


    public function delete($todoID){
        try {
            Todo::findOrFail($todoID)->delete();
            session()->flash('success', 'Todo deleted successfully!');
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Todo not found or unable to delete.');
        } catch (Exception $e) {
            session()->flash('error', 'Failed to delete todo!');
        }
    }




    public function toggle($todoID){
        $todo=Todo::find($todoID);
        $todo->completed=!$todo->completed;
        $todo->save();
    }

    public function edit($todoID){
        $todo = Todo::find($todoID);
    
        if ($todo) {
            $this->editingTodoID = $todoID;
            $this->editingTodoName = $todo->name;
        }
    }

    public function cancelEdit(){
        $this->reset('editingTodoID','editingTodoName');
    }

    public function update(){
        $this -> ValidateOnly('editingTodoName');
        Todo::find($this->editingTodoID)->update([
            'name' => $this->editingTodoName
        ]);
        $this->cancelEdit();
    }


    public function render()
    {
        return view('livewire.todo-list',[
            // 'todos'=>Todo::latest()->get(),
            'todos'=>Todo::latest()->where('name','like',"%{$this->search}%")->paginate(5),
        ]);
    }
}
