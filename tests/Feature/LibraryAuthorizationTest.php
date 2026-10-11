<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $memberUser;

    private User $librarianUser;

    private User $adminUser;

    private Book $circulatingBook;

    private Book $referenceBook;

    private Loan $activeLoan;

    protected function setUp(): void
    {
        parent::setUp();

        $author = Author::factory()->create();

        $this->circulatingBook = Book::factory()->for($author)->create([
            'is_reference' => false,
        ]);

        $this->referenceBook = Book::factory()->reference()->for($author)->create();

        $this->memberUser = User::factory()->member()->create();
        Member::factory()->create([
            'user_id' => $this->memberUser->id,
            'email' => $this->memberUser->email,
        ]);

        $this->librarianUser = User::factory()->librarian()->create();
        $this->adminUser = User::factory()->admin()->create();

        $otherMember = Member::factory()->create();
        $this->activeLoan = Loan::factory()->unreturned()->create([
            'book_id' => $this->circulatingBook->id,
            'member_id' => $otherMember->id,
        ]);
    }

    /**
     * Test Guest row of the test matrix.
     */
    public function test_guest_permissions_matrix(): void
    {
        // 1. GET /books -> 200
        $this->get('/books')->assertStatus(200);

        // 2. GET /books/{book} -> 200
        $this->get("/books/{$this->circulatingBook->id}")->assertStatus(200);

        // 3. GET /books/create -> 302 (redirect to login)
        $this->get('/books/create')->assertRedirect('/login');

        // 4. POST /books/{book}/borrow -> 302 (redirect to login)
        $this->post("/books/{$this->circulatingBook->id}/borrow")->assertRedirect('/login');

        // 5. GET /my/loans -> 302 (redirect to login)
        $this->get('/my/loans')->assertRedirect('/login');

        // 6. GET /desk -> 302 (redirect to login)
        $this->get('/desk')->assertRedirect('/login');

        // 7. POST /desk/loans/{loan}/return -> 302 (redirect to login)
        $this->post("/desk/loans/{$this->activeLoan->id}/return")->assertRedirect('/login');

        // 8. GET /admin/users -> 302 (redirect to login)
        $this->get('/admin/users')->assertRedirect('/login');
    }

    /**
     * Test Member row of the test matrix.
     */
    public function test_member_permissions_matrix(): void
    {
        $freshBook = Book::factory()->create(['is_reference' => false]);

        // 1. GET /books -> 200
        $this->actingAs($this->memberUser)->get('/books')->assertStatus(200);

        // 2. GET /books/{book} -> 200
        $this->actingAs($this->memberUser)->get("/books/{$this->circulatingBook->id}")->assertStatus(200);

        // 3. GET /books/create -> 403 Forbidden
        $this->actingAs($this->memberUser)->get('/books/create')->assertStatus(403);

        // 4. POST /books/{book}/borrow -> 302 (redirect to /my/loans)
        $this->actingAs($this->memberUser)->post("/books/{$freshBook->id}/borrow")
            ->assertRedirect(route('loans.my'));
        $this->assertDatabaseHas('book_member', [
            'book_id' => $freshBook->id,
            'member_id' => $this->memberUser->member->id,
            'returned_at' => null,
        ]);

        // 5. GET /my/loans -> 200 OK
        $this->actingAs($this->memberUser)->get('/my/loans')->assertStatus(200);

        // 6. GET /desk -> 403 Forbidden
        $this->actingAs($this->memberUser)->get('/desk')->assertStatus(403);

        // 7. POST /desk/loans/{loan}/return -> 403 Forbidden
        $this->actingAs($this->memberUser)->post("/desk/loans/{$this->activeLoan->id}/return")->assertStatus(403);

        // 8. GET /admin/users -> 403 Forbidden
        $this->actingAs($this->memberUser)->get('/admin/users')->assertStatus(403);
    }

    /**
     * Test Librarian row of the test matrix.
     */
    public function test_librarian_permissions_matrix(): void
    {
        $freshBook = Book::factory()->create(['is_reference' => false]);

        // 1. GET /books -> 200
        $this->actingAs($this->librarianUser)->get('/books')->assertStatus(200);

        // 2. GET /books/{book} -> 200
        $this->actingAs($this->librarianUser)->get("/books/{$this->circulatingBook->id}")->assertStatus(200);

        // 3. GET /books/create -> 200 OK
        $this->actingAs($this->librarianUser)->get('/books/create')->assertStatus(200);

        // 4. POST /books/{book}/borrow -> 302
        $this->actingAs($this->librarianUser)->post("/books/{$freshBook->id}/borrow")
            ->assertRedirect(route('loans.my'));

        // 5. GET /my/loans -> 200 OK
        $this->actingAs($this->librarianUser)->get('/my/loans')->assertStatus(200);

        // 6. GET /desk -> 200 OK
        $this->actingAs($this->librarianUser)->get('/desk')->assertStatus(200);

        // 7. POST /desk/loans/{loan}/return -> 302 (marks as returned)
        $this->actingAs($this->librarianUser)->post("/desk/loans/{$this->activeLoan->id}/return")
            ->assertRedirect();
        $this->assertNotNull($this->activeLoan->fresh()->returned_at);

        // 8. GET /admin/users -> 403 Forbidden
        $this->actingAs($this->librarianUser)->get('/admin/users')->assertStatus(403);
    }

    /**
     * Test Admin row of the test matrix.
     */
    public function test_admin_permissions_matrix(): void
    {
        $freshBook = Book::factory()->create(['is_reference' => false]);

        // 1. GET /books -> 200
        $this->actingAs($this->adminUser)->get('/books')->assertStatus(200);

        // 2. GET /books/{book} -> 200
        $this->actingAs($this->adminUser)->get("/books/{$this->circulatingBook->id}")->assertStatus(200);

        // 3. GET /books/create -> 200 OK
        $this->actingAs($this->adminUser)->get('/books/create')->assertStatus(200);

        // 4. POST /books/{book}/borrow -> 302
        $this->actingAs($this->adminUser)->post("/books/{$freshBook->id}/borrow")
            ->assertRedirect(route('loans.my'));

        // 5. GET /my/loans -> 200 OK
        $this->actingAs($this->adminUser)->get('/my/loans')->assertStatus(200);

        // 6. GET /desk -> 200 OK
        $this->actingAs($this->adminUser)->get('/desk')->assertStatus(200);

        // 7. POST /desk/loans/{loan}/return -> 302
        $this->actingAs($this->adminUser)->post("/desk/loans/{$this->activeLoan->id}/return")
            ->assertRedirect();

        // 8. GET /admin/users -> 200 OK
        $this->actingAs($this->adminUser)->get('/admin/users')->assertStatus(200);
    }

    /**
     * Reference books cannot be borrowed.
     */
    public function test_reference_book_cannot_be_borrowed(): void
    {
        $this->actingAs($this->memberUser)
            ->post("/books/{$this->referenceBook->id}/borrow")
            ->assertStatus(403);

        $this->actingAs($this->librarianUser)
            ->post("/books/{$this->referenceBook->id}/borrow")
            ->assertStatus(403);

        $this->actingAs($this->adminUser)
            ->post("/books/{$this->referenceBook->id}/borrow")
            ->assertStatus(403);
    }

    /**
     * Books with active unreturned loans cannot be deleted.
     */
    public function test_book_with_active_loans_cannot_be_deleted(): void
    {
        $this->actingAs($this->librarianUser)
            ->delete("/books/{$this->circulatingBook->id}")
            ->assertStatus(403);

        // If returned, it can be deleted
        $this->activeLoan->update(['returned_at' => now()]);
        $this->actingAs($this->librarianUser)
            ->delete("/books/{$this->circulatingBook->id}")
            ->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $this->circulatingBook->id]);
    }

    /**
     * Admins can change other users' roles, but cannot demote themselves.
     */
    public function test_admin_role_change_and_self_demotion_prevention(): void
    {
        // Admin changing another user's role to librarian
        $this->actingAs($this->adminUser)
            ->patch("/admin/users/{$this->memberUser->id}/role", [
                'role' => 'librarian',
            ])
            ->assertRedirect();
        $this->assertSame('librarian', $this->memberUser->fresh()->role);

        // Admin attempting to demote themselves to member -> 403 Forbidden
        $this->actingAs($this->adminUser)
            ->patch("/admin/users/{$this->adminUser->id}/role", [
                'role' => 'member',
            ])
            ->assertStatus(403);
        $this->assertSame('admin', $this->adminUser->fresh()->role);

        // Non-admin attempting to change roles -> 403 Forbidden
        $this->actingAs($this->memberUser)
            ->patch("/admin/users/{$this->librarianUser->id}/role", [
                'role' => 'admin',
            ])
            ->assertStatus(403);
    }
}
