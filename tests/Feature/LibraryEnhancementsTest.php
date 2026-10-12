<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $memberUser;

    private Member $member;

    private User $librarianUser;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memberUser = User::factory()->member()->create();
        $this->member = Member::factory()->create([
            'user_id' => $this->memberUser->id,
            'email' => $this->memberUser->email,
        ]);

        $this->librarianUser = User::factory()->librarian()->create();
        $this->adminUser = User::factory()->admin()->create();
    }

    // ---------------------------------------------------------
    // Phase 1: Due Dates & Overdue Tracking
    // ---------------------------------------------------------

    public function test_borrowing_a_book_sets_due_date_to_14_days(): void
    {
        $book = Book::factory()->create(['is_reference' => false]);

        $response = $this->actingAs($this->memberUser)
            ->post(route('books.borrow', $book));

        $response->assertRedirect(route('loans.my'));

        $loan = Loan::where('book_id', $book->id)
            ->where('member_id', $this->member->id)
            ->first();

        $this->assertNotNull($loan);
        $this->assertNotNull($loan->due_at);
        $this->assertEquals(
            now()->addDays(14)->format('Y-m-d'),
            $loan->due_at->format('Y-m-d')
        );
    }

    public function test_overdue_helper_and_desk_filter(): void
    {
        $book = Book::factory()->create();

        $overdueLoan = Loan::factory()->unreturned()->create([
            'book_id' => $book->id,
            'member_id' => $this->member->id,
            'borrowed_at' => now()->subDays(20),
            'due_at' => now()->subDays(6),
        ]);

        $this->assertTrue($overdueLoan->isOverdue());
        $this->assertGreaterThanOrEqual(5, $overdueLoan->daysOverdue());

        // Librarian viewing overdue filter
        $response = $this->actingAs($this->librarianUser)
            ->get(route('desk.index', ['status' => 'overdue']));

        $response->assertOk();
        $response->assertSee($book->title);
        $response->assertSee('Overdue (');
    }

    // ---------------------------------------------------------
    // Phase 2: Catalog Search & Multi-criteria Filtering
    // ---------------------------------------------------------

    public function test_catalog_search_by_title_isbn_and_author(): void
    {
        $authorA = Author::factory()->create(['name' => 'Jane Austen']);
        $authorB = Author::factory()->create(['name' => 'Arthur Conan Doyle']);

        $bookA = Book::factory()->for($authorA)->create([
            'title' => 'Pride and Prejudice',
            'isbn' => '9780141439518',
        ]);
        $bookB = Book::factory()->for($authorB)->create([
            'title' => 'A Study in Scarlet',
            'isbn' => '9780140439083',
        ]);

        // Search by title keyword
        $responseTitle = $this->get(route('books.index', ['q' => 'Prejudice']));
        $responseTitle->assertOk();
        $responseTitle->assertSee('Pride and Prejudice');
        $responseTitle->assertDontSee('A Study in Scarlet');

        // Search by ISBN
        $responseIsbn = $this->get(route('books.index', ['q' => '9780140439083']));
        $responseIsbn->assertOk();
        $responseIsbn->assertSee('A Study in Scarlet');
        $responseIsbn->assertDontSee('Pride and Prejudice');

        // Search by Author name
        $responseAuthor = $this->get(route('books.index', ['q' => 'Austen']));
        $responseAuthor->assertOk();
        $responseAuthor->assertSee('Pride and Prejudice');
        $responseAuthor->assertDontSee('A Study in Scarlet');
    }

    public function test_catalog_filtering_by_status_and_author(): void
    {
        $author = Author::factory()->create();
        $availableBook = Book::factory()->for($author)->create(['is_reference' => false, 'title' => 'Clean Code']);
        $borrowedBook = Book::factory()->for($author)->create(['is_reference' => false, 'title' => 'Refactoring']);
        $referenceBook = Book::factory()->reference()->for($author)->create(['title' => 'Oxford Dictionary']);

        Loan::factory()->unreturned()->create([
            'book_id' => $borrowedBook->id,
            'member_id' => $this->member->id,
        ]);

        // Filter available
        $respAvailable = $this->get(route('books.index', ['status' => 'available']));
        $respAvailable->assertOk();
        $respAvailable->assertSee('Clean Code');
        $respAvailable->assertDontSee('Refactoring');
        $respAvailable->assertDontSee('Oxford Dictionary');

        // Filter borrowed
        $respBorrowed = $this->get(route('books.index', ['status' => 'borrowed']));
        $respBorrowed->assertOk();
        $respBorrowed->assertSee('Refactoring');
        $respBorrowed->assertDontSee('Clean Code');

        // Filter reference
        $respRef = $this->get(route('books.index', ['status' => 'reference']));
        $respRef->assertOk();
        $respRef->assertSee('Oxford Dictionary');
        $respRef->assertDontSee('Clean Code');
    }

    // ---------------------------------------------------------
    // Phase 3: Book Cover Uploads
    // ---------------------------------------------------------

    public function test_librarian_can_upload_and_update_book_cover(): void
    {
        Storage::fake('public');

        $author = Author::factory()->create();
        $cover = UploadedFile::fake()->image('cover.jpg', 400, 600);

        // Store new book with cover (ISBN must be 13 characters)
        $response = $this->actingAs($this->librarianUser)
            ->post(route('books.store'), [
                'title' => 'Design Patterns',
                'author_id' => $author->id,
                'isbn' => '9780201633610',
                'published_year' => 1994,
                'is_reference' => false,
                'cover' => $cover,
            ]);

        $book = Book::where('isbn', '9780201633610')->firstOrFail();
        $response->assertRedirect(route('books.show', $book));

        $this->assertNotNull($book->cover_path);
        Storage::disk('public')->assertExists($book->cover_path);

        // Update cover
        $newCover = UploadedFile::fake()->image('new_cover.png', 400, 600);
        $oldPath = $book->cover_path;

        $updateResponse = $this->actingAs($this->librarianUser)
            ->put(route('books.update', $book), [
                'title' => 'Design Patterns (2nd Ed)',
                'author_id' => $author->id,
                'isbn' => '9780201633610',
                'published_year' => 1995,
                'is_reference' => false,
                'cover' => $newCover,
            ]);

        $updateResponse->assertRedirect(route('books.show', $book));
        $book->refresh();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($book->cover_path);
    }

    public function test_cover_upload_validation_rejects_invalid_file(): void
    {
        Storage::fake('public');
        $author = Author::factory()->create();

        $invalidFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->librarianUser)
            ->post(route('books.store'), [
                'title' => 'Invalid Book',
                'author_id' => $author->id,
                'isbn' => '9780000000000',
                'published_year' => 2024,
                'is_reference' => false,
                'cover' => $invalidFile,
            ]);

        $response->assertSessionHasErrors(['cover']);
    }

    // ---------------------------------------------------------
    // Phase 4: Book Holds / Reservations
    // ---------------------------------------------------------

    public function test_member_can_place_a_hold_on_borrowed_book(): void
    {
        $book = Book::factory()->create(['is_reference' => false]);
        $otherMember = Member::factory()->create();

        Loan::factory()->unreturned()->create([
            'book_id' => $book->id,
            'member_id' => $otherMember->id,
        ]);

        $response = $this->actingAs($this->memberUser)
            ->from(route('books.show', $book))
            ->post(route('books.reserve', $book));

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('reservations', [
            'book_id' => $book->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);
    }

    public function test_member_cannot_hold_reference_or_available_book(): void
    {
        $refBook = Book::factory()->reference()->create();
        $availBook = Book::factory()->create(['is_reference' => false]);

        // Attempt hold on reference book (policy prevents)
        $respRef = $this->actingAs($this->memberUser)
            ->post(route('books.reserve', $refBook));
        $respRef->assertForbidden();

        // Attempt hold on available book (policy prevents)
        $respAvail = $this->actingAs($this->memberUser)
            ->post(route('books.reserve', $availBook));
        $respAvail->assertForbidden();
    }

    public function test_member_cannot_place_duplicate_active_holds(): void
    {
        $book = Book::factory()->create(['is_reference' => false]);
        $otherMember = Member::factory()->create();

        Loan::factory()->unreturned()->create([
            'book_id' => $book->id,
            'member_id' => $otherMember->id,
        ]);

        Reservation::factory()->create([
            'book_id' => $book->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        // Duplicate hold attempt is forbidden by ReservationPolicy
        $response = $this->actingAs($this->memberUser)
            ->post(route('books.reserve', $book));

        $response->assertForbidden();
    }

    public function test_member_can_cancel_own_hold_but_not_others(): void
    {
        $book = Book::factory()->create();
        $otherMember = Member::factory()->create();

        $myHold = Reservation::factory()->create([
            'book_id' => $book->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $otherHold = Reservation::factory()->create([
            'book_id' => $book->id,
            'member_id' => $otherMember->id,
            'status' => 'pending',
        ]);

        // Attempt canceling other member's hold -> 403 Forbidden
        $unauthorizedResp = $this->actingAs($this->memberUser)
            ->delete(route('reservations.destroy', $otherHold));
        $unauthorizedResp->assertForbidden();

        // Cancel own hold -> Success
        $authorizedResp = $this->actingAs($this->memberUser)
            ->from(route('books.show', $book))
            ->delete(route('reservations.destroy', $myHold));
        $authorizedResp->assertRedirect(route('books.show', $book));
        $authorizedResp->assertSessionHas('status', 'Reservation cancelled.');

        $this->assertEquals('cancelled', $myHold->fresh()->status);
    }
}
