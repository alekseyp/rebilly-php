<?php
/**
 * This source file is proprietary and part of Rebilly.
 *
 * (c) Rebilly SRL
 *     Rebilly Ltd.
 *     Rebilly Inc.
 *
 * @see https://www.rebilly.com
 */

namespace Rebilly\Services;

use ArrayObject;
use JsonSerializable;
use Rebilly\Entities\Organization;
use Rebilly\Http\Exception\NotFoundException;
use Rebilly\Http\Exception\UnprocessableEntityException;
use Rebilly\Paginator;
use Rebilly\Rest\Collection;
use Rebilly\Rest\Service;

/**
 * Class OrganizationService
 *
 */
final class OrganizationService extends Service
{
    /**
     * @param array|ArrayObject $params
     *
     * @return Organization[][]|Collection[]|Paginator
     */
    public function paginator($params = [])
    {
        return new Paginator($this->client(), 'organizations', $params);
    }

    /**
     * @param array|ArrayObject $params
     *
     * @return Organization[]|Collection
     */
    public function search($params = [])
    {
        return $this->client()->get('organizations', $params);
    }

    /**
     * @param string $organizationId
     * @param array|ArrayObject $params
     *
     * @throws NotFoundException The resource data does not exist
     *
     * @return Organization
     */
    public function load($organizationId, $params = [])
    {
        return $this->client()->get('organizations/{organizationId}', ['organizationId' => $organizationId] + (array) $params);
    }

    /**
     * @param array|JsonSerializable|Organization $data
     *
     * @throws UnprocessableEntityException The input data does not valid
     *
     * @return Organization
     */
    public function create($data)
    {
        return $this->client()->post($data, 'organizations');
    }

    /**
     * @param string $organizationId
     * @param array|JsonSerializable|Organization $data
     *
     * @throws UnprocessableEntityException The input data does not valid
     *
     * @return Organization
     */
    public function update($organizationId, $data)
    {
        return $this->client()->patch($data, 'organizations/{organizationId}', ['organizationId' => $organizationId]);
    }
}
