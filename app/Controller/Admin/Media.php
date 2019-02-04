<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use App\Model\Admin\Medium;
use Slim\Http\Request;
use Slim\Http\Response;

class Media extends Base
{
    public function upload(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            $files = $request->getUploadedFiles();
            foreach ($files['media'] as $file) {
                if($file->getError() === UPLOAD_ERR_OK) {
                    $check = Medium::where('name', $file->getClientFilename())->first();
                    if($check === null) {
                        $file->moveTo($this->container->get('settings')['app']['media'] . '/' . $file->getClientFilename());
                        Medium::insert([
                            'name' => $file->getClientFilename(),
                            'width' => $this->image($this->container->get('settings')['app']['media'] . '/' . $file->getClientFilename())->width(),
                            'height' => $this->image($this->container->get('settings')['app']['media'] . '/' . $file->getClientFilename())->height(),
                            'size' => $this->image($this->container->get('settings')['app']['media'] . '/' . $file->getClientFilename())->filesize() / 1024,
                            'created_at' => $this->time::now(),
                            'updated_at' => $this->time::now()
                        ]);
                    }
                }
            }
            return $response->withRedirect('/admin/media', 301);
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function rename(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            $validation = $this->validate($request, [
                'id' => 'required',
                'name' => 'required|max:191'
            ]);
            if($validation === null) {
                $name = htmlspecialchars(trim($request->getParam('name')));
                $check = Medium::where('name', $name)->first();
                if($check === null) {
                    $id = htmlspecialchars(trim($request->getParam('id')));
                    $this->filesystem->rename($this->container->get('settings')['app']['media'] . '/' . Medium::where('id', $id)->value('name'), $this->container->get('settings')['app']['media'] . '/' . $name);
                    Medium::where('id', $id)->update([
                        'name' => $name
                    ]);
                    return $response->withRedirect('/admin/media', 301);
                } else {
                    $this->data['error'] = "Cannot use that name";
                    return $this->view($response->withStatus(400), 'common/templates/validation.twig');
                }
            } else {
                $this->data['error'] = reset($validation);
                return $this->view($response->withStatus(400), 'common/templates/validation.twig');
            }
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function delete(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            $validation = $this->validate($request, [
                'id' => 'required'
            ]);
            if($validation === null) {
                $id = htmlspecialchars(trim($request->getParam('id')));
                $this->filesystem->remove($this->container->get('settings')['app']['media'] . '/' . Medium::where('id', $id)->value('name'));
                Medium::where('id', $id)->delete();
                return $response->withRedirect('/admin/media', 301);
            } else {
                $this->data['error'] = reset($validation);
                return $this->view($response->withStatus(400), 'common/templates/validation.twig');
            }
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }
}
