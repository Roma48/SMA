<?php

namespace CTO\AppBundle\AWS;

use Aws\S3\S3Client;

class S3
{
    const ACL_PRIVATE = 'private';
    const ACL_PRIVATE_READ = 'public-read';
    const ACL_PRIVATE_READ_WRITE = 'public-read-write';

    /** @var S3Client */
    protected $s3;

    /** @var  string name Bucket in Aws */
    protected $bucket;

    protected $s3url;

    public function __construct($key, $secret, $bucket, $region, $s3url)
    {
        $this->s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $key,
                'secret' => $secret
            ]
        ]);
        $this->bucket = $bucket;
        $this->s3url = $s3url;
    }

    /**
     * @param $filename
     * @param $filePath
     * @param string $mimeType
     * @return \Aws\Result|null
     */
    public function upload($filename, $filePath, $mimeType = 'image/jpeg')
    {
        $result = null;
        try {
            $result = $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $filename,
                'SourceFile' => $filePath,
                'ACL'    => self::ACL_PRIVATE_READ,
                'ContentType' => $mimeType
            ]);
        } catch(\Exception $e){};

        return $result;
    }

    /**
     * @param $filename
     * @return \Aws\Result|null
     */
    public function remove($filename)
    {
        $result = null;
        try{
            $result = $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $filename,
            ]);
        } catch(\Exception $e){};

        return $result;
    }

    public function getUrl($filename)
    {
        return $this->s3url . $filename;
    }
}
