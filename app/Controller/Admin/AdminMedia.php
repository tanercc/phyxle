<?php

namespace App\Controller\Admin;

use App\Controller\Common\CommonBase;
use App\Model\Admin\AdminMedium;
use Intervention\Image\Constraint;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminMedia extends CommonBase
{
    /**
     * Upload functions
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function upload(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Get input values
        $media = $request->getUploadedFiles();

        // Upload media
        foreach($media['media'] as $medium) {
            // Check if there are any errors when uploading
            if($medium->getError() !== UPLOAD_ERR_OK) {
                continue;
            }

            // Get medium name
            $name = $medium->getClientFilename();

            // Check if medium name is already in use
            $checkName = AdminMedium::where('name', $name)->first();

            if($checkName !== null) {
                continue;
            }

            // Get medium original and thumbnail paths
            $original = $this->container->get('settings')['app']['media'] . "/originals/" . $name;
            $thumbnail = $this->container->get('settings')['app']['media'] . "/thumbnails/" . $name;

            // Move medium to original path
            $medium->moveTo($original);

            // Get details of original medium
            $width = $this->image($original)->width();
            $height = $this->image($original)->height();
            $size = $this->image($original)->filesize() / 1024;

            // Resize and move medium to thumbnail path
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

            // Update name, width, height, size, created at and updated at
            // columns in admin media table
            AdminMedium::insert([
                'name' => $name,
                'width' => $width,
                'height' => $height,
                'size' => $size,
                'created_at' => $this->time::now(),
                'updated_at' => $this->time::now()
            ]);

            // Redirect to /admin/media route
            return $response->withRedirect('/admin/media');
        }
    }

    /**
     * Rename functions
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function rename(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validations are failed
        $validation = $this->validator($request, [
            'id' => 'required',
            'name' => 'required|max:191'
        ]);

        if($validation !== null) {
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $id = htmlspecialchars(trim($request->getParam('id')));
        $name = htmlspecialchars(trim($request->getParam('name')));

        // Check if medium name is already in use
        $checkName = AdminMedium::where('name', $name)->first();

        if($checkName !== null) {
            $this->data = [
                'title' => 'There is a Medium Already Using That Name',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get old and new paths for media
        $originalOld = $this->container->get('settings')['app']['media'] . '/originals/' . AdminMedium::where('id', $id)->value('name');
        $originalNew = $this->container->get('settings')['app']['media'] . '/originals/' . $name;
        $thumbnailOld = $this->container->get('settings')['app']['media'] . '/thumbnails/' . AdminMedium::where('id', $id)->value('name');
        $thumbnailNew = $this->container->get('settings')['app']['media'] . '/thumbnails/' . $name;

        // Rename media
        $this->filesystem->rename($originalOld, $originalNew);
        $this->filesystem->rename($thumbnailOld, $thumbnailNew);

        // Update name column in admin media table
        AdminMedium::where('id', $id)->update([
            'name' => $name
        ]);

        // Redirect to /admin/media route
        return $response->withRedirect('/admin/media');
    }

    /**
     * Delete functions
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validations are failed
        $validation = $this->validator($request, [
            'id' => 'required'
        ]);

        if($validation !== null) {
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $id = htmlspecialchars(trim($request->getParam('id')));

        // Get media paths
        $original = $this->container->get('settings')['app']['media'] . '/originals/' . AdminMedium::where('id', $id)->value('name');
        $thumbnail = $this->container->get('settings')['app']['media'] . '/thumbnails/' . AdminMedium::where('id', $id)->value('name');

        // Remove media
        $this->filesystem->remove($original);
        $this->filesystem->remove($thumbnail);

        // Delete current medium row in admin media table
        AdminMedium::where('id', $id)->delete();

        // Redirect to /admin/media table
        return $response->withRedirect('/admin/media');
    }
}
