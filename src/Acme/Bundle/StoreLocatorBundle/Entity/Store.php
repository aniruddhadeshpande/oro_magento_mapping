<?php

namespace Acme\Bundle\StoreLocatorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\CreatedAtAwareTrait;
use Oro\Bundle\EntityBundle\EntityProperty\UpdatedAtAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\UpdatedAtAwareTrait;

/**
 * A physical store shown in the store locator.
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="acme_store",
 *      indexes={@ORM\Index(name="acme_store_active_idx", columns={"active"})}
 * )
 */
class Store implements CreatedAtAwareInterface, UpdatedAtAwareInterface
{
    use CreatedAtAwareTrait;
    use UpdatedAtAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected ?string $name = null;

    /**
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    protected ?string $street = null;

    /**
     * @ORM\Column(name="city", type="string", length=100)
     */
    protected ?string $city = null;

    /**
     * @ORM\Column(name="postcode", type="string", length=20, nullable=true)
     */
    protected ?string $postcode = null;

    /**
     * @ORM\Column(name="phone", type="string", length=50, nullable=true)
     */
    protected ?string $phone = null;

    /**
     * @ORM\Column(name="opening_hours", type="text", nullable=true)
     */
    protected ?string $openingHours = null;

    /**
     * @ORM\Column(name="active", type="boolean", options={"default"=true})
     */
    protected bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    public function setOpeningHours(?string $openingHours): self
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
