<?php

namespace App\Infrastructure\DTO\Params\QueryParams;

use Symfony\Component\Validator\Constraints as Assert;

class EntityQueryParamsDto extends PaginatorQueryParamsDto
{
    public function __construct(
        ?int           $page = 1,
        ?int           $perPage = 20,

        #[Assert\Length(max: 120)]
        public ?string $name = null,

        #[Assert\Email]
        public ?string $email = null,

        public ?int    $status = null,
    )
    {
        parent::__construct($page, $perPage);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $result = [
            ...parent::toArray(),
        ];
        if ($this->name != null) {
            $result['name'] = $this->name;
        }
        if ($this->email != null) {
            $result['email'] = $this->email;
        }
        if ($this->status !== null) {
            $result['status'] = $this->status;
        }
        return $result;
    }
}
