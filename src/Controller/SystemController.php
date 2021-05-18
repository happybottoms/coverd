<?php

namespace App\Controller;

use App\Configuration\AppConfiguration;
use App\Entity\EAV\Attribute;
use App\Entity\EAV\AttributeDefinition;
use App\Entity\Setting;
use App\Transformers\UserTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SystemController
 *
 * @Route(path="/api/system")
 */
class SystemController extends BaseController
{
    /**
     * Get a list of Attribute Types
     *
     * @Route(path="/attribute-types", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function attributeTypes(Request $request)
    {
        $types = AttributeDefinition::getAttributeTypes();
        $attributes = array_map(function ($type) {
            return Attribute::getTypeMetaData($type);
        }, $types);

        return new JsonResponse(['data' => $attributes]);
    }

    /**
     * @param Request $request
     *
     * @Route(path="/settings", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function listSettings(Request $request)
    {
        $return_arr = $this->getSettings();

        return new JsonResponse(['data' => $return_arr]);
    }

    /**
     * @param Request $request
     *
     * @Route(path="/settings", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function updateSettings(Request $request, AppConfiguration $appConfig)
    {
        $params = $this->getParams($request);

        foreach ($params as $key => $param) {
            $appConfig->set($key, $param);
        }

        $this->getEm()->flush();

        return new JsonResponse(['data' => $this->getSettings()]);
    }

    /**
     * @Route(path="/current-user", methods={"GET"})
     * @return JsonResponse
     */
    public function getLoggedInUser(Request $request)
    {
        return $this->serialize($request, $this->getUser(), new UserTransformer());
    }

    private function getSettings()
    {
        $settings = $this->getRepository(Setting::class)->findAll();

        $return_arr = [];
        foreach ($settings as $setting) {
            $return_arr[$setting->getConfig()] = $setting->getValue();
        }

        return $return_arr;
    }
}
