<?php

namespace Drupal\developer_directory_db_demo\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use InvalidArgumentException;

/**
 * Manages developer records using Drupal Database API.
 */
class DeveloperRecordManager {
  /**
   * Database connection and time service are injected by Drupal.
   */
  public function __construct(protected Connection $database, protected TimeInterface $time,) {}

  /**
   * Returns the table containing developer records.
   */
  private function developerTable(): string {
    return 'developer_directory_db_demo_developer';
  }

  /**
   * Returns the table containing developer technologies.
   */
  private function technologyTable(): string {
    return 'developer_directory_db_demo_technology';
  }

  /**
   * Validates the experience level.
   */
  private function validateExperience(string $experience): void {
    $allowed = ['junior', 'mid', 'senior'];

    if (!in_array($experience, $allowed, TRUE)) {
      throw new InvalidArgumentException('Experience must be junior, mid or senior.');
    }
  }

  /**
   * Returns all developers, ordered by name.
   */
  public function getAll(): array {
    $developers = $this->database
      ->select($this->developerTable(), 'd')
      ->fields('d')
      ->orderBy('name', 'ASC')
      ->execute()
      ->fetchAllAssoc('id');

    // Attach each developer's technologies.
    foreach ($developers as $developer) {
      $developer->technologies = $this->getTechnologies((int) $developer->id);
    }

    return $developers;
  }

  /**
   * Returns one developer, or NULL if it does not exist.
   */
  public function get(int $id): ?object {
    $developer = $this->database
      ->select($this->developerTable(), 'd')
      ->fields('d')
      ->condition('id', $id)
      ->execute()
      ->fetchObject();

    if (!$developer) {
      return NULL;
    }

    $developer->technologies = $this->getTechnologies(
      (int) $developer->id
    );

    return $developer;
  }

  /**
   * Returns the technologies associated with a developer.
   */
  public function getTechnologies(int $developer_id): array {
    return $this->database
      ->select($this->technologyTable(), 't')
      ->fields('t', ['technology'])
      ->condition('developer_id', $developer_id)
      ->orderBy('technology', 'ASC')
      ->execute()
      ->fetchCol();
  }

  /**
   * Inserts a developer and their technologies.
   *
   * Returns the new developer ID.
   */
  public function insert(array $values): int {
    $experience = (string) ($values['experience'] ?? '');
    $this->validateExperience($experience);

    $transaction = $this->database->startTransaction();

    try {
      $id = (int) $this->database
        ->insert($this->developerTable())
        ->fields([
          'name' => $values['name'],
          'email' => $values['email'],
          'experience' => $experience,
          'available' => (int) ($values['available'] ?? 0),
          'portfolio_uri' => $values['portfolio_uri'] ?? NULL,
          'portfolio_title' => $values['portfolio_title'] ?? NULL,
          'created' => $this->time->getRequestTime(),
        ])
        ->execute();

      $this->replaceTechnologies(
        $id,
        $values['technologies'] ?? []
      );

      return $id;
    }
    catch (\Throwable $exception) {
      $transaction->rollBack();
      throw $exception;
    }
  }

  /**
   * Updates a developer and replaces their technologies.
   *
   * Returns the number of affected developer rows.
   */
  public function update(int $id, array $values): int {
    $experience = (string) ($values['experience'] ?? '');
    $this->validateExperience($experience);

    $transaction = $this->database->startTransaction();

    try {
      $affected = $this->database
        ->update($this->developerTable())
        ->fields([
          'name' => $values['name'],
          'email' => $values['email'],
          'experience' => $experience,
          'available' => (int) ($values['available'] ?? 0),
          'portfolio_uri' => $values['portfolio_uri'] ?? NULL,
          'portfolio_title' => $values['portfolio_title'] ?? NULL,
        ])
        ->condition('id', $id)
        ->execute();

      // Replace the previous technology list.
      $this->replaceTechnologies(
        $id,
        $values['technologies'] ?? []
      );

      return $affected;
    }
    catch (\Throwable $exception) {
      $transaction->rollBack();
      throw $exception;
    }
  }

  /**
   * Replaces the technologies associated with a developer.
   */
  private function replaceTechnologies(int $developer_id, array $technologies,): void {
    // Remove the existing associations first.
    $this->database
      ->delete($this->technologyTable())
      ->condition('developer_id', $developer_id)
      ->execute();

    // Remove empty values and duplicate technology names.
    $technologies = array_unique(
      array_filter($technologies, static fn ($value) =>
        is_string($value) && $value !== ''
      )
    );

    foreach ($technologies as $technology) {
      $this->database
        ->insert($this->technologyTable())
        ->fields([
          'developer_id' => $developer_id,
          'technology' => $technology,
        ])
        ->execute();
    }
  }

  /**
   * Deletes a developer and their technology associations.
   *
   * Returns the number of deleted developer rows.
   */
  public function delete(int $id): int {
    $transaction = $this->database->startTransaction();

    try {
      $this->database
        ->delete($this->technologyTable())
        ->condition('developer_id', $id)
        ->execute();

      return $this->database
        ->delete($this->developerTable())
        ->condition('id', $id)
        ->execute();
    }
    catch (\Throwable $exception) {
      $transaction->rollBack();
      throw $exception;
    }
  }
}