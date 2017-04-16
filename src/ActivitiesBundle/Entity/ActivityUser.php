<?php

namespace ActivitiesBundle\Entity;


use Doctrine\ORM\Mapping as ORM;


/**
 * ActivityUser
 *
 * @ORM\Table(name="activitiesUsers")
 * @ORM\Entity(repositoryClass="ActivitiesBundle\Repository\ActivityUserRepository")
 */
class ActivityUser
{

  /**
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

   /**
   * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
   * @ORM\JoinColumn(nullable=false)
   */
  private $user;

  /**
   * @ORM\ManyToOne(targetEntity="ActivitiesBundle\Entity\Activity")
   * @ORM\JoinColumn(nullable=false)
   */
  private $activity;

  /**
   * @ORM\Column(name="otherParticipation", type="boolean")
   */
  private $otherParticipation;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set otherParticipation
     *
     * @param boolean $otherParticipation
     * @return ActivityUser
     */
    public function setOtherParticipation($otherParticipation)
    {
        $this->otherParticipation = $otherParticipation;

        return $this;
    }

    /**
     * Get otherParticipation
     *
     * @return boolean 
     */
    public function getOtherParticipation()
    {
        return $this->otherParticipation;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     * @return ActivityUser
     */
    public function setUser(\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set activity
     *
     * @param \ActivitiesBundle\Entity\Activity $activity
     * @return ActivityUser
     */
    public function setActivity(\ActivitiesBundle\Entity\Activity $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \ActivitiesBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
