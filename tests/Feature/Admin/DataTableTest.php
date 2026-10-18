<?php

use App\Livewire\Admin\Rooms\Index;
use App\Models\Group;
use App\Models\Room;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

it('recherche et trie les lignes', function () {
    Room::factory()->create(['name' => 'Portail']);
    Room::factory()->create(['name' => 'Mur']);

    Livewire::test(Index::class)
        ->assertSee('Portail')->assertSee('Mur')
        ->set('search', 'Port')
        ->assertSee('Portail')->assertDontSee('Mur')
        ->set('search', '')
        ->call('sortBy', 'name')
        ->assertSeeInOrder(['Mur', 'Portail'])
        ->call('sortBy', 'name')
        ->assertSeeInOrder(['Portail', 'Mur']);
});

it('crée une salle depuis la modale', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('showForm', true)
        ->set('form.name', 'Jardin')
        ->set('form.capacity', 12)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false)
        ->assertDispatched('toast');

    expect(Room::query()->where('name', 'Jardin')->value('capacity'))->toBe(12);
});

it('valide en temps réel et refuse plus de 15 places', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.capacity', 20)
        ->assertHasErrors(['form.capacity' => 'max'])
        ->set('form.name', '')
        ->assertHasErrors(['form.name' => 'required']);
});

it('modifie une salle existante', function () {
    $room = Room::factory()->create(['name' => 'Mur', 'capacity' => 15]);

    Livewire::test(Index::class)
        ->call('edit', $room->id)
        ->assertSet('form.name', 'Mur')
        ->set('form.capacity', 10)
        ->call('save')
        ->assertHasNoErrors();

    expect($room->fresh()->capacity)->toBe(10);
});

it('refuse de supprimer une salle encore utilisée', function () {
    $group = Group::factory()->create();

    Livewire::test(Index::class)
        ->call('delete', $group->room_id)
        ->assertDispatched('toast', type: 'error');

    expect(Room::query()->whereKey($group->room_id)->exists())->toBeTrue();
});

it('exporte le tableau en Excel', function () {
    Room::factory()->create(['name' => 'Portail']);

    Livewire::test(Index::class)
        ->call('export')
        ->assertFileDownloaded('salles-'.now()->format('Y-m-d').'.xlsx');
});
