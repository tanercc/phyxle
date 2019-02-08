<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use App\Model\Admin\Medium;
use Intervention\Image\Constraint;
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
                    $name = $file->getClientFilename();
                    $check = Medium::where('name', $name)->first();
                    if($check === null) {
                        $original = $this->container->get('settings')['app']['media'] . "/originals/" . $name;
                        $thumbnail = $this->container->get('settings')['app']['media'] . "/thumbnails/" . $name;
                        $file->moveTo($original);
                        $width = $this->image($original)->width();
                        $height = $this->image($original)->height();
                        if($width > $height) {
                            if($width > 500) {
                                $this->image($original)->resize(500, null, function(Constraint $constraint) {
                                    $constraint->aspectRatio();
                                })->save($thumbnail);
                            }
                            if($width <= 500) {
                                $this->image($original)->save($thumbnail);
                            }
                        }
                        if($width < $height) {
                            if($height > 500) {
                                $this->image($original)->resize(null, 500, function(Constraint $constraint) {
                                    $constraint->aspectRatio();
                                })->save($thumbnail);
                            }
                            if($height <= 500) {
                                $this->image($original)->save($thumbnail);
                            }
                        }
                        if($width === $height) {
                            if($width > 500) {
                                $this->image($original)->resize(500, 500)->save($thumbnail);
                            }
                            if($width <= 500) {
                                $this->image($original)->save($thumbnail);
                            }
                        }
                        $size = $this->image($original)->filesize() / 1024;
                        Medium::insert([
                            'name' => $name,
                            'width' => $width,
                            'height' => $height,
                            'size' => $size,
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
            $validation = $this->validator($request, [
                'id' => 'required',
                'name' => 'required|max:191'
            ]);
            if($validation === null) {
                $name = htmlspecialchars(trim($request->getParam('name')));
                $check = Medium::where('name', $name)->first();
                if($check === null) {
                    $id = htmlspecialchars(trim($request->getParam('id')));
                    $originalOld = $this->container->get('settings')['app']['media'] . '/originals/' . Medium::where('id', $id)->value('name');
                    $originalNew = $this->container->get('settings')['app']['media'] . '/originals/' . $name;
                    $thumbnailOld = $this->container->get('settings')['app']['media'] . '/thumbnails/' . Medium::where('id', $id)->value('name');
                    $thumbnailNew = $this->container->get('settings')['app']['media'] . '/thumbnails/' . $name;
                    $this->filesystem->rename($originalOld, $originalNew);
                    $this->filesystem->rename($thumbnailOld, $thumbnailNew);
                    Medium::where('id', $id)->update([
                        'name' => $name
                    ]);
                    return $response->withRedirect('/admin/media', 301);
                } else {
                    $this->data['error'] = "Cannot Use That Name";
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
            $validation = $this->validator($request, [
                'id' => 'required'
            ]);
            if($validation === null) {
                $id = htmlspecialchars(trim($request->getParam('id')));
                $original = $this->container->get('settings')['app']['media'] . '/originals/' . Medium::where('id', $id)->value('name');
                $thumbnail = $this->container->get('settings')['app']['media'] . '/thumbnails/' . Medium::where('id', $id)->value('name');
                $this->filesystem->remove($original);
                $this->filesystem->remove($thumbnail);
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
