pipeline {
    agent {
        label "podman"
    }
    stages {
        stage('Build Position Size Calculator Image') {
            steps {
                sh 'bash build_image.bash'
            }
        }
        
    }
}
